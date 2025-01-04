<?php

namespace App\Console\Commands\Forms;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TrashInvalidTypeFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:trash-invalid
                            {--save : Выполнить удаление}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление осмотров с невалидным типом';

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
        $query = DB::table('anketas')
            ->where('type_anketa', 'pak')
            ->orWhere('type_anketa', 'vid_pl')
            ->orWhere('type_anketa', 'Dop');

        $this->info("Записей с невалидным типом: " . $query->count());

        if (!$this->option('save')) {
            return;
        }

        $query->delete();
        $this->warn('Записи удалены');
    }
}
