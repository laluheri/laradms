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
        $schedule->call('App\Http\Controllers\AdmsController@insertToAttendance')->everyMinute();
        // $schedule->call('App\Http\Controllers\AdmsController@insertEmpAtt')->dailyAt('15:24');
        // $schedule->call('App\Http\Controllers\AdmsController@index')->dailyAt('10:58')->onFailure(function (\Throwable $e) {
        // $schedule->call('App\Http\Controllers\AdmsController@insertEmpAtt')->dailyAt('11:07')->onFailure(function (\Throwable $e) {
            // Catat log error di sini menggunakan Log::error atau metode lainnya
        //     Log::error('Scheduled job error: ' . $e->getMessage());
        // });
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