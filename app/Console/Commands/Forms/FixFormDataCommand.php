<?php

namespace App\Console\Commands\Forms;

use App\Anketa;
use App\Car;
use App\Company;
use App\Driver;
use App\Enums\FlagPakEnum;
use App\Enums\FormFixStatusEnum;
use App\Enums\FormTypeEnum;
use App\Models\Forms\Form;
use App\Point;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Throwable;

class FixFormDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:fix
                            {--count : Количество необработанных осмотров}
                            {--reset-invalid : Сброс статуса валидации у невалидных осмотров }
                            {--force : Запуск команды игнорируя конфиг}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Фикс данных осмотров';

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
        if ($this->option('count')) {
            $nonFixedForms = Anketa::query()
                ->where('fix_status', FormFixStatusEnum::UNPROCESSED)
                ->count();

            $this->log("Всего необработанных осмотров - $nonFixedForms");

            return;
        }

        if ($this->option('reset-invalid')) {
            $this->resetInvalid();

            return;
        }

        if ((config('forms.fix', false) === false) && !$this->option('force')) {
            return;
        }

        set_time_limit(0);

        $chunkSize = config('forms.fix-chunk-size', 30000);

        try {
            $fixedForms = $this->fixForms($chunkSize);

            $this->log("Обработано осмотров - $fixedForms");
        } catch (Throwable $exception) {
            $this->log("Ошибка обработки группы осмотров - " . $exception->getMessage());
        }
    }

    private function resetInvalid()
    {
        $invalidForms = Anketa::query()
            ->select([
                'uuid',
                'id'
            ])
            ->where('fix_status', '>', FormFixStatusEnum::FIXED)
            ->get();

        foreach ($invalidForms as $anketa) {
            try {
                DB::beginTransaction();

                $anketa->setAttribute('transfer_status', false);
                $anketa->setAttribute('fix_status', FormFixStatusEnum::UNPROCESSED);

                $anketa->save();

                $form = Form::withTrashed()->find($anketa->uuid);
                if (!$form) {
                    continue;
                }

                $form->details()->delete();
                $form->forceDelete();

                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();

                $this->log("Ошибка сброса статусов у невалидных осмотров - " . $exception->getMessage());
            }
        }

        $this->log("Сброшено статусов у невалидных осмотров - {$invalidForms->count()}");
    }

    private function log(string $message)
    {
        $this->info($message);
        Log::info($message);
    }

    private function fixForms(int $chunkSize): int
    {
        $cars = Car::withTrashed()->select(['id', 'hash_id', 'company_id'])->get();
        $drivers = Driver::withTrashed()->select(['id', 'hash_id', 'company_id'])->get();
        $companies = Company::withTrashed()->select(['id', 'hash_id'])->get();
        $points = Point::withTrashed()->select(['id', 'hash_id'])->get();
        $users = User::withTrashed()
            ->select([
                'users.id',
                'users.hash_id',
            ])
            ->get();

        $maps = [
            'cars' => [
                'hash_id_by_hash_id' => $cars->pluck('hash_id', 'hash_id')->toArray(),
                'hash_id_by_id' => $cars->pluck('hash_id', 'id')->toArray()
            ],
            'drivers' => [
                'hash_id_by_hash_id' => $drivers->pluck('hash_id', 'hash_id')->toArray(),
                'hash_id_by_id' => $drivers->pluck('hash_id', 'id')->toArray()
            ],
            'points' => [
                'id_by_hash_id' => $points->pluck('id', 'hash_id')->toArray(),
                'id_by_id' => $points->pluck('id', 'id')->toArray()
            ],
            'companies' => [
                'hash_id_by_hash_id' => $companies->pluck('hash_id', 'hash_id')->toArray(),
                'hash_id_by_id' => $companies->pluck('hash_id', 'id')->toArray(),
                'id_by_driver_hash_id' => $drivers->pluck('company_id', 'hash_id')->toArray(),
                'id_by_car_hash_id' => $cars->pluck('company_id', 'hash_id')->toArray(),
            ],
            'users' => [
                'id_by_id' => $users->pluck('id', 'id')->toArray(),
                'id_by_hash_id' => $users->pluck('id', 'hash_id')->toArray()
            ]
        ];

        $forms = Anketa::query()
            ->where('fix_status', FormFixStatusEnum::UNPROCESSED)
            ->orderBy('id', 'desc')
            ->limit($chunkSize)
            ->get();

        foreach ($forms as $form) {
            $this->fixForm($form, $maps);
        }

        return $forms->count();
    }

    private function getPointIdBySameId($id, array $maps)
    {
        return $this->getPointIdById($id, $maps) ??
            $this->getPointIdByHashId($id, $maps) ??
            null;
    }

    private function getPointIdById($id, array $maps)
    {
        return $maps['points']['id_by_id'][$id] ?? null;
    }

    private function getPointIdByHashId($id, array $maps)
    {
        return $maps['points']['id_by_hash_id'][$id] ?? null;
    }

    private function getCompanyHashIdBySameId($id, array $maps)
    {
        return $this->getCompanyHashIdByHashId($id, $maps) ??
            $this->getCompanyHashIdById($id, $maps) ??
            null;
    }

    private function getCompanyHashIdByHashId($id, array $maps)
    {
        return $maps['companies']['hash_id_by_hash_id'][$id] ?? null;
    }

    private function getCompanyHashIdById($id, array $maps)
    {
        return $maps['companies']['hash_id_by_id'][$id] ?? null;
    }

    private function getCarHashIdBySameId($id, array $maps)
    {
        return $this->getCarHashIdByHashId($id, $maps) ??
            $this->getCarHashIdById($id, $maps) ??
            null;
    }

    private function getCarHashIdByHashId($id, array $maps)
    {
        return $maps['cars']['hash_id_by_hash_id'][$id] ?? null;
    }

    private function getCarHashIdById($id, array $maps)
    {
        return $maps['cars']['hash_id_by_id'][$id] ?? null;
    }

    private function getDriverHashIdBySameId($id, array $maps)
    {
        return $this->getDriverHashIdByHashId($id, $maps) ??
            $this->getDriverHashIdById($id, $maps) ??
            null;
    }

    private function getDriverHashIdByHashId($id, array $maps)
    {
        return $maps['drivers']['hash_id_by_hash_id'][$id] ?? null;
    }

    private function getDriverHashIdById($id, array $maps)
    {
        return $maps['drivers']['hash_id_by_id'][$id] ?? null;
    }

    private function getCompanyIdByCarHashId($id, array $maps)
    {
        return $maps['companies']['id_by_car_hash_id'][$id] ?? null;
    }

    private function getCompanyIdByDriverHashId($id, array $maps)
    {
        return $maps['companies']['id_by_driver_hash_id'][$id] ?? null;
    }

    private function getUserIdBySameId($id, array $maps)
    {
        return $this->getUserIdById($id, $maps) ??
            $this->getUserIdByHashId($id, $maps) ??
            null;
    }

    private function getUserIdById($id, array $maps)
    {
        return $maps['users']['id_by_id'][$id] ?? null;
    }

    private function getUserIdByHashId($id, array $maps)
    {
        return $maps['users']['id_by_hash_id'][$id] ?? null;
    }

    private function fixForm(Anketa $form, array $maps)
    {
        $statuses = [];

        if ($form->point_id === 0) {
            $form->point_id = null;
        }

        if ($form->point_id) {
            $form->point_id = $this->getPointIdBySameId($form->point_id, $maps);
        }

        if (!$form->point_id && $form->pv_id) {
            $point = Point::withTrashed()->where('name', 'like', "%$form->pv_id%")->first();
            if ($point) {
                $form->point_id = $point->id;
            }
        }

        if (!$form->point_id) {
            $statuses[] = FormFixStatusEnum::INVALID_POINT_ID;
        }

        if ($form->driver_id === 0) {
            $form->driver_id = null;
        }

        if ($form->driver_id) {
            $form->driver_id = $this->getDriverHashIdBySameId($form->driver_id, $maps);
        }

        if (!$form->driver_id && $form->driver_fio) {
            $driver = Driver::withTrashed()->where('fio', $form->driver_fio)->first();
            if ($driver) {
                $form->driver_id = $driver->hash_id;
            }
        }

        if (!$form->driver_id && !$form->is_dop) {
            $statuses[] = FormFixStatusEnum::INVALID_DRIVER_ID;
        }

        if ($form->car_id === 0) {
            $form->car_id = null;
        }

        if ($form->car_id) {
            $form->car_id = $this->getCarHashIdBySameId($form->car_id, $maps);
        }

        if (!$form->car_id && $form->car_gos_number && ($form->type_anketa === FormTypeEnum::TECH)) {
            $car = Car::withTrashed()->where('gos_number', $form->car_gos_number)->first();
            if ($car) {
                $form->car_id = $car->hash_id;
            }
        }

        if (($form->type_anketa === FormTypeEnum::TECH) && !$form->car_id && !$form->is_dop) {
            $statuses[] = FormFixStatusEnum::INVALID_CAR_ID;
        }

        if ($form->company_id === 0) {
            $form->company_id = null;
        }

        if ($form->company_id) {
            $form->company_id = $this->getCompanyHashIdBySameId($form->company_id, $maps);
        }

        if (!$form->company_id && $form->driver_id) {
            $form->company_id = $this->getCompanyHashIdById($this->getCompanyIdByDriverHashId($form->driver_id, $maps), $maps);
        }

        if (!$form->company_id && $form->car_id) {
            $form->company_id = $this->getCompanyHashIdById($this->getCompanyIdByCarHashId($form->car_id, $maps), $maps);
        }

        if (!$form->company_id && $form->company_name) {
            $company = Company::withTrashed()->where('name', $form->company_name)->first();
            if ($company) {
                $form->company_id = $company->hash_id;
            }
        }

        if (!$form->company_id) {
            $statuses[] = FormFixStatusEnum::INVALID_COMPANY_ID;
        }

        if ($form->user_id) {
            $form->user_id = $this->getUserIdBySameId($form->user_id, $maps);
        }

        if (!$form->user_id) {
            $statuses[] = FormFixStatusEnum::INVALID_USER_ID;
        }

        if (($form->type_anketa === FormTypeEnum::MEDIC) && $form->terminal_id && !$form->flag_pak) {
            $form->flag_pak = FlagPakEnum::SDPO_A;
        }

        if ($form->terminal_id === 0) {
            $form->terminal_id = null;
        }

        if (($form->type_anketa === FormTypeEnum::MEDIC) && !$form->terminal_id && $form->flag_pak) {
            $userTerminal = User::withTrashed()->where('id', $form->user_id)->first();
            if ($userTerminal && $userTerminal->hasRole('terminal')) {
                $form->terminal_id = $userTerminal->id;
            } else {
                $statuses[] = FormFixStatusEnum::INVALID_TERMINAL_ID;
            }
        }

        if (!$form->created_at || !$form->date) {
            $form->realy = 'нет';
        } else {
            $date = Carbon::parse($form->created_at)->timestamp - Carbon::parse($form->date)->timestamp;
            $diffInHours = abs($date) / 3600;
            if ($diffInHours >= 12) {
                $form->realy = 'нет';
            } else {
                $form->realy = 'да';
            }
        }

        if ($form->in_cart && !$form->deleted_at) {
            $form->deleted_at = $form->updated_at || $form->created_at;
        }

        if ($form->deleted_id) {
            $form->deleted_id = $this->getUserIdBySameId($form->deleted_id, $maps);
        }

        if ($form->user_id && $form->date && $form->user_eds) {
            $user = User::withTrashed()->where('id', $form->user_id)->first();
            if ($user && $user->eds && $user->validity_eds_start && $user->validity_eds_end) {
                $date = Carbon::parse($form->date)->timestamp;
                $edsStart = Carbon::parse($user->validity_eds_start)->timestamp;
                $edsEnd = Carbon::parse($user->validity_eds_end)->timestamp;

                if (($edsStart < $date) && ($date < $edsEnd)) {
                    $form->user_eds = $user->eds;
                    $form->user_validity_eds_start = $user->validity_eds_start;
                    $form->user_validity_eds_end = $user->validity_eds_end;
                }
            }
        }

        if (!$form->uuid) {
            $form->uuid = Uuid::uuid4();
        }

        $form->fix_status = FormFIxStatusConverter::fromStatuses($statuses);

        $form->save();
    }
}
