<?php

namespace App\Console;

use App\Anketa;
use App\Company;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands
        = [
            //
        ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $startLowLoadPeriod = '00:15';
        $endLowLoadPeriod = '05:00';

        $schedule->command('forms:fix')->withoutOverlapping()->everyMinute()->between($startLowLoadPeriod, $endLowLoadPeriod);
        $schedule->command('forms:transfer')->withoutOverlapping()->everyMinute()->between($startLowLoadPeriod, $endLowLoadPeriod);
        $schedule->command('forms:restore-foreign')->withoutOverlapping()->everyMinute()->between($startLowLoadPeriod, $endLowLoadPeriod);
        $schedule->command('companies:inspect')->monthlyOn(1, '6:00');
        $schedule->command("run:briefings")->monthlyOn(10, '10:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
