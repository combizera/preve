<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class InactivityService
{
    /**
     * Recurring-generated transactions don't count as activity: only a
     * transaction the user created by hand proves they are still engaged.
     * Cached because the dashboard banner checks this on every request;
     * TransactionObserver invalidates it on manual create/delete.
     */
    public function lastManualActivity(User $user): ?CarbonImmutable
    {
        $latest = Cache::remember(
            $this->cacheKey($user->id),
            CarbonImmutable::now()->addHour(),
            fn (): string => (string) $user
                ->transactions()
                ->whereNull('recurring_transaction_id')
                ->max('created_at'),
        );

        return $latest === '' ? null : CarbonImmutable::parse($latest);
    }

    public function forgetLastManualActivity(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    public function inactiveDays(User $user): ?int
    {
        $last = $this->lastManualActivity($user);

        return $last instanceof CarbonImmutable ? (int) $last->diffInDays(CarbonImmutable::now()) : null;
    }

    /**
     * Users who never created a transaction are an onboarding problem,
     * not a re-engagement one — they are never considered inactive here.
     */
    public function isInactive(User $user, ?int $days = null): bool
    {
        $last = $this->lastManualActivity($user);

        return $last instanceof CarbonImmutable && $last->lt($this->cutoff($days));
    }

    public function hasActiveHiatus(User $user): bool
    {
        return $user->hiatuses()->covering(CarbonImmutable::now())->exists();
    }

    /**
     * @return array{inactive_days: int, last_manual_activity: string}|null
     */
    public function bannerPayload(User $user): ?array
    {
        if (!$this->shouldShowBanner($user)) {
            return null;
        }

        return [
            'inactive_days'        => (int) $this->inactiveDays($user),
            'last_manual_activity' => $this->lastManualActivity($user)->toDateString(),
        ];
    }

    public function shouldShowBanner(User $user): bool
    {
        return $this->isInactive($user)
            && !$this->bannerRecentlyDismissed($user)
            && !$this->hasActiveHiatus($user);
    }

    public function dismissBanner(User $user): void
    {
        $user->forceFill(['reengagement_banner_dismissed_at' => CarbonImmutable::now()])->save();
    }

    /**
     * Total of recurring-generated expenses created after the given moment —
     * the concrete "your subscriptions kept running" number for the email.
     */
    public function recurringExpensesSince(User $user, CarbonImmutable $since): int
    {
        $today = CarbonImmutable::today()->toDateString();

        return (int) $user
            ->transactions()
            ->whereNotNull('recurring_transaction_id')
            ->where('type', TransactionType::EXPENSE)
            ->where('created_at', '>', $since)
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $cardScope) => $cardScope
                    ->whereNotNull('purchase_date')
                    ->where('purchase_date', '<=', $today))
                ->orWhere(fn (Builder $cashScope) => $cashScope
                    ->whereNull('purchase_date')
                    ->where('transaction_date', '<=', $today)))
            ->sum('amount');
    }

    /**
     * Inactive users who should actually receive the re-engagement email:
     * verified, outside the cooldown window, and not on a declared hiatus.
     *
     * @return Builder<User>
     */
    public function emailableUsersQuery(?int $days = null): Builder
    {
        $cooldown = CarbonImmutable::now()
            ->subDays((int) config('preve.reengagement_email_cooldown_days'));
        $today = CarbonImmutable::today()->toDateString();

        return $this->inactiveUsersQuery($days)
            ->whereNotNull('email_verified_at')
            ->where(fn (Builder $query) => $query
                ->whereNull('reengagement_notified_at')
                ->orWhere('reengagement_notified_at', '<', $cooldown))
            ->whereDoesntHave('hiatuses', fn (Builder $query) => $query
                ->where('started_at', '<=', $today)
                ->where(fn (Builder $activeScope) => $activeScope
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $today)))
            ->whereDoesntHave('hiatuses', fn (Builder $query) => $query
                ->where('reconciled_at', '>=', $cooldown));
    }

    /**
     * @return Builder<User>
     */
    public function inactiveUsersQuery(?int $days = null): Builder
    {
        $cutoff = $this->cutoff($days);

        return User::query()
            ->whereHas('transactions', fn (Builder $query): Builder => $query
                ->whereNull('recurring_transaction_id'))
            ->whereDoesntHave('transactions', fn (Builder $query): Builder => $query
                ->whereNull('recurring_transaction_id')
                ->where('created_at', '>=', $cutoff));
    }

    private function bannerRecentlyDismissed(User $user): bool
    {
        $dismissedAt = $user->reengagement_banner_dismissed_at;

        return $dismissedAt !== null
            && $dismissedAt->gt(CarbonImmutable::now()->subDays((int) config('preve.banner_dismiss_days')));
    }

    private function cutoff(?int $days): CarbonImmutable
    {
        $days ??= (int) config('preve.inactivity_days');

        return CarbonImmutable::now()->subDays($days);
    }

    private function cacheKey(int $userId): string
    {
        return "reengagement:last-manual-activity:{$userId}";
    }
}
