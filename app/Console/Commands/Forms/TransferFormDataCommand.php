<?php

namespace App\Console\Commands\Forms;

use App\Anketa;
use App\Company;
use App\Enums\FormFixStatusEnum;
use App\Enums\FormTypeEnum;
use App\Models\Forms\BddForm;
use App\Models\Forms\Form;
use App\Models\Forms\MedicForm;
use App\Models\Forms\PrintPlForm;
use App\Models\Forms\ReportCartForm;
use App\Models\Forms\TechForm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransferFormDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:transfer
                            {--count : Количество необработанных осмотров}
                            {--inc : Обновление инкремента в новой таблице}
                            {--invalid : Перенос невалидных осмотров }
                            {--force : Запуск команды игнорируя конфиг}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перенос данных осмотров';

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
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('inc')) {
            $database = config('database.connections.' . config('database.default') . '.database');
            $currentIdQuery = DB::raw("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '". $database ."' AND TABLE_NAME = 'anketas'");
            $currentId = DB::select($currentIdQuery)[0]->AUTO_INCREMENT;

            DB::update("ALTER TABLE forms AUTO_INCREMENT = ". $currentId . ";");

            $this->log("Авто-инкремент таблицы forms перемещен на последнее значение из таблицы anketas: $currentId");

            return;
        }

        if ($this->option('count')) {
            $nonFixedForms = Anketa::query()
                ->where('fix_status', FormFixStatusEnum::FIXED)
                ->where('transfer_status', false)
                ->count();

            $this->log("Всего необработанных осмотров - $nonFixedForms");

            return;
        }

        if ((config('forms.transfer', false) === false) && !$this->option('force')) {
            return;
        }

        set_time_limit(0);

        $chunkSize = config('forms.transfer-chunk-size', 2500);

        try {
            $fixedForms = $this->transferForms($chunkSize);

            $this->log("Перенесено осмотров - $fixedForms");
        } catch (Throwable $exception) {
            $this->log("Ошибка переноса группы осмотров - " . $exception->getMessage());
        }
    }

    private function log(string $message)
    {
        $this->info($message);
        Log::info($message);
    }

    /**
     * @throws Throwable
     */
    private function transferForms(int $chunkSize): int
    {
        $maps = [
            'companies' => Company::withTrashed()->where('hash_id', 'like', '0%')->get()->pluck('hash_id', 'hash_id')->toArray()
        ];

        $query = Anketa::query()
            ->where('transfer_status', false);

        if ($this->option('invalid')) {
            $query->where('fix_status', '!=', FormFixStatusEnum::UNPROCESSED);
        } else {
            $query->where('fix_status', FormFixStatusEnum::FIXED);
        }

        $forms = $query->orderBy('id')
            ->limit($chunkSize)
            ->get();

        foreach ($forms as $form) {
            try {
                DB::beginTransaction();

                $this->transferForm($form, $maps);

                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();

                $form->update([
                    'fix_status' => 0
                ]);

                $this->log("Ошибка переноса осмотра - " . $exception->getMessage());
            }
        }

        return $forms->count();
    }

    private function transferForm(Anketa $form, array $maps)
    {
        $formData = $form->toArray();

        $formData['forms_uuid'] = $form->uuid;

        if ($form->company_id && isset($maps['companies']['0'.$form->company_id])) {
            $formData['company_id'] = $maps['companies']['0'.$form->company_id];
        }

        if (!$form->is_dop) {
            $formData['is_dop'] = 0;
        }

        if ($form->user_id === 0) {
            $formData['user_id'] = null;
        }

        Form::create($formData);

        switch ($form->type_anketa) {
            case FormTypeEnum::MEDIC:
                MedicForm::create($formData);
                break;
            case FormTypeEnum::TECH:
                TechForm::create($formData);
                break;
            case FormTypeEnum::BDD:
                BddForm::create($formData);
                break;
            case FormTypeEnum::PRINT_PL:
                PrintPlForm::create($formData);
                break;
            case FormTypeEnum::REPORT_CARD:
                ReportCartForm::create($formData);
        }

        $form->update([
            'transfer_status' => true
        ]);
    }
}
