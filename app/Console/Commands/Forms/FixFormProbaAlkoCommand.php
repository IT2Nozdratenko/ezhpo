<?php

namespace App\Console\Commands\Forms;

use App\GenerateHashIdTrait;
use App\Models\Forms\MedicForm;
use Illuminate\Console\Command;

class FixFormProbaAlkoCommand extends Command
{
    use GenerateHashIdTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:fix-alcometer-result
                            {--save : Обновить замеры алкоголя}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Фикс уровня алкоголя в крови в осмотрах';

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
        $save = $this->option('save');

        $query = MedicForm::query()
            ->where('alcometer_result', 1)
            ->whereNotNull('flag_pak');

        $this->info(sprintf("Анкет с уровнем алкоголя 1 - %s", $query->count()));

        if (!$save) return;

        $query->update([
            'alcometer_result' => 0.1
        ]);

        $this->info("Уровень алкоголя в анкетах скорректирован до 0.1");
    }
}
