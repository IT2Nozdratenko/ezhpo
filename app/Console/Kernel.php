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
        //todo перенести логику куда нибудь
        $schedule->call(function () {
            // смотрим, в каких компаниях были осмотры за прошлый месяц
            $companiesWithInspection = Anketa::whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
                                             ->whereNotNull('company_id')
                                             ->get(['company_id'])
                                             ->pluck('company_id')
                                             ->unique();

            Company::whereIn('hash_id', $companiesWithInspection)
                   ->update(['has_actived_prev_month' => 'Да']);

            Company::whereNotIn('hash_id', $companiesWithInspection)
                   ->update(['has_actived_prev_month' => 'Нет']);

        })->monthlyOn(1, '6:00');
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
