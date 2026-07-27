<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\SupportedCurrency;
use App\Models\User;
use App\Services\InactivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReengagementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $inactivity = resolve(InactivityService::class);

        $days = (int) $inactivity->inactiveDays($notifiable);
        $lastActivity = $inactivity->lastManualActivity($notifiable);
        $recurringTotal = $lastActivity === null
            ? 0
            : $inactivity->recurringExpensesSince($notifiable, $lastActivity);

        $message = (new MailMessage())
            ->subject(__('mail.reengagement.subject', ['days' => $days]))
            ->greeting(__('mail.reengagement.greeting', ['name' => $notifiable->name]))
            ->line(__('mail.reengagement.intro', ['days' => $days]));

        if ($recurringTotal > 0) {
            $message->line(__('mail.reengagement.recurring', [
                'amount' => $this->formatAmount($notifiable, $recurringTotal),
            ]));
        }

        return $message
            ->action(__('mail.reengagement.cta'), route('reconciliation.show'))
            ->line(__('mail.reengagement.outro'));
    }

    private function formatAmount(User $notifiable, int $cents): string
    {
        $currency = $notifiable->currency ?? SupportedCurrency::BRL;
        $isEnglish = $notifiable->preferredLocale() === 'en';

        return $currency->symbol() . ' ' . number_format(
            $cents / 100,
            2,
            $isEnglish ? '.' : ',',
            $isEnglish ? ',' : '.',
        );
    }
}
