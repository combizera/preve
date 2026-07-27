<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupportedCurrency;
use App\Observers\UserObserver;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property Carbon|null $reengagement_banner_dismissed_at
 * @property Carbon|null $reengagement_notified_at
 */
#[ObservedBy([UserObserver::class])]
#[Fillable([
    'name',
    'email',
    'password',
    'locale',
    'currency',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
final class User extends Authenticatable implements HasLocalePreference
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public function preferredLocale(): string
    {
        return $this->locale ?? config('app.locale');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<Tag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return HasMany<RecurringTransaction, $this>
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * @return HasMany<Forecast, $this>
     */
    public function forecasts(): HasMany
    {
        return $this->hasMany(Forecast::class);
    }

    /**
     * @return HasMany<ForecastSeries, $this>
     */
    public function forecastSeries(): HasMany
    {
        return $this->hasMany(ForecastSeries::class);
    }

    /**
     * @return HasMany<SavingsBucket, $this>
     */
    public function savingsBuckets(): HasMany
    {
        return $this->hasMany(SavingsBucket::class);
    }

    /**
     * @return HasMany<CreditCard, $this>
     */
    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    /**
     * @return HasMany<Hiatus, $this>
     */
    public function hiatuses(): HasMany
    {
        return $this->hasMany(Hiatus::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'                => 'datetime',
            'password'                         => 'hashed',
            'two_factor_confirmed_at'          => 'datetime',
            'currency'                         => SupportedCurrency::class,
            'reengagement_banner_dismissed_at' => 'datetime',
            'reengagement_notified_at'         => 'datetime',
        ];
    }
}
