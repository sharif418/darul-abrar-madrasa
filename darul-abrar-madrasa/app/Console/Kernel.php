<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * Register console commands so they are discoverable and usable.
     */
    protected $commands = [
        // Commands are auto-discovered from app/Console/Commands directory
    ];

    /**
     * Define the application's command schedule.
     *
     * This wires the periodic jobs:
     * - fees:apply-late-fees -> daily
     * - fees:send-reminders  -> daily at 09:00
     * - sync:spatie-roles    -> weekly verification (Sundays at 03:00)
     */
    protected function schedule(Schedule $schedule): void
    {
        // Apply late fees to overdue fees daily (midnight by default)
        $schedule->command('fees:apply-late-fees')->daily();

        // Send fee reminders to guardians every morning at 9 AM
        $schedule->command('fees:send-reminders')->dailyAt('09:00');

        // Verify Spatie role synchronization weekly (Sundays at 3 AM)
        // This runs in verification mode only - does not auto-repair
        // Logs results for admin review to detect role drift
        $schedule->command('sync:spatie-roles')
            ->weekly()
            ->sundays()
            ->at('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Autoload all commands in the Commands directory
        $this->load(__DIR__ . '/Commands');

        // Load route-based Artisan commands if present
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
