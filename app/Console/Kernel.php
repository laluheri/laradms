<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        Commands\DemoCron::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->call('App\Http\Controllers\AdmsController@index')->dailyAt('06:27');
        $schedule->call('App\Http\Controllers\AdmsController@index')->everyMinute();
        // $schedule->command('daily:insert')->everyMinute();
        // $schedule->command('daily:insert')->dailyAt('15:25');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}