<?php

namespace App\Console\Commands\Forms;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TrashOldFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:trash-old
                            {days=90 : Максимальное количество дней в корзине}
                            {--save : Выполнить удаление}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление старых осмотров';

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
        $days = $this->argument('days');

        $query = DB::table('anketas')
            ->where('in_cart', 1)
            ->where(function (Builder $query) use ($days) {
                $query
                    ->where(function (Builder $subQuery) use ($days) {
                        $subQuery
                            ->whereNull('deleted_at')
                            ->whereRaw("updated_at <= (NOW() + INTERVAL -$days DAY)");
                    })
                    ->orWhere(function (Builder $subQuery) use ($days) {
                        $subQuery
                            ->whereNotNull('deleted_at')
                            ->whereRaw("deleted_at <= (NOW() + INTERVAL -$days DAY)");
                    });
            });

        $this->info("Записей старше $days дн. в корзине: " . $query->count());

        if (!$this->option('save')) {
            return;
        }

        $query->delete();
        $this->warn('Записи удалены');
    }
}
