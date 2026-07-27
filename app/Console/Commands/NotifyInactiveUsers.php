<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ReengagementNotification;
use App\Services\InactivityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

final class NotifyInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:notify-inactive
        {--days= : Inactivity threshold in days (defaults to config preve.inactivity_days)}
        {--dry-run : List who would be notified without sending anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the re-engagement email to users without manual transactions past the threshold.';

    public function handle(InactivityService $inactivity): void
    {
        $days = $this->option('days') === null ? null : (int) $this->option('days');
        $query = $inactivity->emailableUsersQuery($days);

        if ($this->option('dry-run')) {
            $emails = $query->pluck('email');

            $this->info("{$emails->count()} user(s) would be notified:");
            $emails->each(fn (string $email) => $this->line("  - {$email}"));

            return;
        }

        $notified = 0;

        $query->chunkById(100, function ($users) use (&$notified): void {
            foreach ($users as $user) {
                /** @var User $user */
                $user->notify(new ReengagementNotification());
                $user->forceFill(['reengagement_notified_at' => Date::now()])->save();
                $notified++;
            }
        });

        $this->info("{$notified} re-engagement email(s) queued.");
    }
}
