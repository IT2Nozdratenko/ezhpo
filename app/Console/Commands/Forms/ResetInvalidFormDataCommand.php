<?php

namespace App\Console\Commands\Forms;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetInvalidFormDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:reset-invalid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сброс невалидных внешних ключей и таймштампов осмотров';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->resetNullFK();
        $this->resetDeletedAtTimestamp();
    }

    private function resetDeletedAtTimestamp()
    {
        $counter = DB::table('anketas')
            ->where('in_cart', '<>', 1)
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null]);

        $this->info("Сброшено таймштампов удаления у записей не в корзине: $counter");
    }

    private function resetNullFK()
    {
        $counter = DB::table('anketas')
            ->where('driver_id', 0)
            ->update(['driver_id' => null]);

        $this->info("Сброшено записей с ID водителя - 0: $counter");

        $counter = DB::table('anketas')
            ->where('car_id', '')
            ->orWhere('car_id', '0')
            ->update(['car_id' => null]);

        $this->info("Сброшено записей с ID авто - 0: $counter");

        $counter = DB::table('anketas')
            ->where('point_id', 0)
            ->update(['point_id' => null]);

        $this->info("Сброшено записей с ID ПВ - 0: $counter");

        $counter = DB::table('anketas')
            ->where('company_id', 0)
            ->update(['company_id' => null]);

        $this->info("Сброшено записей с ID компании - 0: $counter");

        $counter = DB::table('anketas')
            ->where('terminal_id', 0)
            ->update(['terminal_id' => null]);

        $this->info("Сброшено записей с ID терминала - 0: $counter");
    }
}
