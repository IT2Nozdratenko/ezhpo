<?php

namespace App\Http\Controllers\Api;

use App\Actions\Anketa\ChangeSdpoMedicFormType\ChangeSdpoMedicFormTypeHandler;
use App\Actions\Anketa\ExportFormsLabelingPdf\ExportFormsLabelingPdfCommand;
use App\Actions\Anketa\ExportFormsLabelingPdf\ExportFormsLabelingPdfHandler;
use App\Actions\Forms\StoreFormEvent\StoreFormEventCommand;
use App\Actions\Forms\StoreFormEvent\StoreFormEventHandler;
use App\Car;
use App\Driver;
use App\Enums\BlockActionReasonsEnum;
use App\Enums\FlagPakEnum;
use App\Enums\FormTypeEnum;
use App\Events\Forms\DriverDismissed;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSdpoCrashRequest;
use App\Http\Requests\StoreSdpoFormFeedbackRequest;
use App\MedicFormNormalizedPressure;
use App\Models\Forms\Form;
use App\Models\Forms\MedicForm;
use App\SdpoCrashLog;
use App\Services\FormHash\FormHashGenerator;
use App\Services\FormHash\MedicHashData;
use App\Settings;
use App\Stamp;
use App\Traits\UserEdsTrait;
use App\User;
use App\ValueObjects\FormFeedback;
use App\ValueObjects\Phone;
use App\ValueObjects\PressureLimits;
use App\ValueObjects\Tonometer;
use DateTimeImmutable;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class SdpoController extends Controller
{
    use UserEdsTrait;

    public function getPrints(Request $request, $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        if ($user->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], 400);
        }

        $driver = Driver::where('hash_id', $id)->first();
        if (!$driver) {
            return response()->json(['message' => 'Водитель с указанным ID не найден!'], 400);
        }

        $forms = Form::query()
            ->select([
                'forms.id',
                'drivers.fio as driver_fio',
                'medic_forms.admitted',
                'forms.date as created_at',
                'forms.user_eds',
                'users.name as user_name',
                'medic_forms.type_view',
                'forms.user_validity_eds_start',
                'forms.user_validity_eds_end',
                'companies.name as stamp_head',
                'stamps.licence as stamp_licence'
            ])
            ->join('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
            ->join('drivers', 'forms.driver_id', '=', 'drivers.hash_id')
            ->join('companies', 'forms.company_id', '=', 'companies.hash_id')
            ->join('users as terminals', 'medic_forms.terminal_id', '=', 'terminals.id')
            ->join('users', 'forms.user_id', '=', 'users.id')
            ->leftJoin('stamps', 'terminals.stamp_id', '=', 'terminals.id')
            ->where('forms.driver_id', $id)
            ->where('forms.type_anketa', FormTypeEnum::MEDIC)
            ->where('medic_forms.admitted', 'Допущен')
            ->whereNotNull('medic_forms.flag_pak')
            ->whereDate('forms.date', '<=', Carbon::now()->toDateTime())
            ->whereDate('forms.date', '>=', Carbon::now()->startOfMonth()->subMonth()->toDateString())
            ->get()
            ->toArray();

        $forms = array_map(function ($form) {
            $validity = UserEdsTrait::getValidityString(
                $form['user_validity_eds_start'] ?? null,
                $form['user_validity_eds_end'] ?? null
            );
            if ($validity) {
                $form['validity'] = $validity;
            }

            return $form;
        }, $forms);

        return response()->json($forms);
    }

    /*
     * Creating anketa by sdpo request
     */
    public function createAnketa(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = $request->user('api');
            $apiClient = $user;
            if ($user->blocked) {
                throw new Exception(BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK), 400);
            }

            if ($request->user_id) {
                $user = User::find($request->user_id);
            }

            if (!$user) {
                throw new Exception('Пользователь с таким ID не найден!', 400);
            }

            /** @var Driver $driver */
            $driver = Driver::where('hash_id', $request->driver_id)->first();
            if (!$driver) {
                throw new Exception('Указанный водитель не найден!', 400);
            }

            date_default_timezone_set('UTC');
            $time = time();
            $timezone = $apiClient->timezone ?: 3;
            $time += $timezone * 3600;
            $time = date('Y-m-d H:i:s', $time);

            if ($driver->end_of_ban && (Carbon::parse($time) < Carbon::parse($driver->end_of_ban))) {
                $message = sprintf("Указанный водитель отстранен до %s!", Carbon::parse($driver->end_of_ban));
                throw new Exception($message, 400);
            }

            if ($driver->dismissed === 'Да') {
                throw new Exception(BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_BLOCK), 303);
            }

            $company = $driver->company;
            if ($company->dismissed === 'Да') {
                $message = BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::COMPANY_BLOCK);
                throw new Exception($message, 401);
            }

            if ($driver->only_offline_medic_inspections) {
                $message = BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_OFFLINE_ONLY);
                throw new Exception($message, 400);
            }

            //TODO: добавить валидацию
            $tonometer = $request->tonometer;
            if (!$tonometer) {
                $tonometer = strval(Tonometer::random($driver));
            }

            $medic = [];
            $medic['type_anketa'] = $request->type_anketa ?? FormTypeEnum::MEDIC;
            $medic['user_id'] = $user->id;
            $medic['user_validity_eds_start'] = $user->validity_eds_start;
            $medic['user_validity_eds_end'] = $user->validity_eds_end;
            $medic['user_eds'] = $user->eds;
            $medic['pulse'] = $request->pulse ?? mt_rand(60, 80);
            $medic['point_id'] = $apiClient->pv->id;
            $medic['tonometer'] = $tonometer;
            $medic['driver_id'] = $driver->hash_id;
            $medic['company_id'] = $company->hash_id;
            $medic['med_view'] = $request->med_view ?? 'В норме';
            $medic['t_people'] = $request->t_people ?? 36.6;
            $medic['type_view'] = $request->type_view ?? 'Предрейсовый/Предсменный';
            $medic['flag_pak'] = $request->type_anketa === FormTypeEnum::PAK_QUEUE ? FlagPakEnum::SDPO_R : FlagPakEnum::SDPO_A;
            $medic['terminal_id'] = $apiClient->id;
            $medic['realy'] = "да";
            $medic['proba_alko'] = $request->proba_alko ?? 'Отрицательно';

            if ($driver->year_birthday !== '' && $driver->year_birthday !== '0000-00-00') {
                $medic['driver_year_birthday'] = $driver->year_birthday;
            }

            if ($request->photo) {
                $medic['photos'] = $request->photo;
            }

            if ($request->video) {
                $medic['videos'] = $request->video;
            }

            $medic['created_at'] = $request->created_at ?? $time;
            $medic['date'] = $request->date ?? $medic['created_at'];

            $test_narko = $request->test_narko ?? 'Отрицательно';
            $driver->date_prmo = $medic['created_at'];

            $admitted = 'Допущен';
            $notAdmittedReasons = [];

            if ($request->filled('alcometer_result')) {
                $medic['alcometer_result'] = doubleval($request->input('alcometer_result'));
            }

            if (($medic['alcometer_result'] ?? 0) == 1) {
                $medic['alcometer_result'] = 0.1;
            }

            if ($request->filled('alcometer_mode')) {
                $medic['alcometer_mode'] = $request->input('alcometer_mode');
            }

            if (($medic['alcometer_result'] ?? 0) > 0) {
                $notAdmittedReasons[] = ['Алкоголь в крови'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
                $medic['proba_alko'] = 'Положительно';
            }

            $proba_alko = $medic['proba_alko'];
            $driver->checkGroupRisk($tonometer, $test_narko, $proba_alko);

            if ($request->sleep_status === 'Нет') {
                $notAdmittedReasons[] = ['sleep_status - нет'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            if ($request->people_status === 'Нет') {
                $notAdmittedReasons[] = ['people_status - нет'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            if ($proba_alko === 'Положительно') {
                $notAdmittedReasons[] = ['Положительный тест на алкоголь'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
                $driver->end_of_ban = Carbon::parse($time)->addMinutes($driver->getTimeOfAlcoholBan());
            }

            if ($test_narko === 'Положительно') {
                $notAdmittedReasons[] = ['Положительный тест на наркотики'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            if ($medic['med_view'] !== 'В норме') {
                $notAdmittedReasons[] = ['med_view не в норме'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            if (doubleval($medic['t_people']) >= 37) {
                $notAdmittedReasons[] = ['Высокая температура'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            if (intval($medic['pulse']) <= $driver->getPulseLower() || intval($medic['pulse']) >= $driver->getPulseUpper()) {
                $notAdmittedReasons[] = ['Слишком высокий или низкий пульс'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
            }

            $pressure = Tonometer::fromString($tonometer);
            $pressureLimits = PressureLimits::create($driver);
            if (!$pressure->isAdmitted($pressureLimits)) {
                $notAdmittedReasons[] = ['Высокое давление'];
                $admitted = 'Не допущен';
                $medic['med_view'] = 'Отстранение';
                $driver->end_of_ban = Carbon::parse($time)->addMinutes($driver->getTimeOfPressureBan());
            }

            $driver->save();

            $medic['admitted'] = $admitted;

            $medic['day_hash'] = FormHashGenerator::generate(new MedicHashData(
                $medic['driver_id'],
                new DateTimeImmutable($medic['date']),
                $medic['type_view']
            ));

            $formModel = new Form();
            $formModel->fill($medic);
            $formModel->save();

            $formDetailsModel = new MedicForm();
            $formDetailsModel->fill($medic);
            $formDetailsModel->setAttribute('forms_uuid', $formModel->uuid);
            $formDetailsModel->save();

            if ($pressure->needNormalize($pressureLimits)) {
                MedicFormNormalizedPressure::store(
                    $formModel->id,
                    $pressure->getNormalized()
                );
            }

            /**
             * ОТПРАВКА УВЕДОМЛЕНИЙ
             */
            $needNotify = $formDetailsModel['admitted'] === 'Не допущен' && $formDetailsModel['flag_pak'] !== FlagPakEnum::SDPO_R;
            if ($needNotify) {
                event(new DriverDismissed($formModel));
            }

            $form = $formModel->toArray() + $formDetailsModel->toArray();

            $form['timeout'] = Settings::setting('timeout') ?? 20;

            $stamp = $apiClient->stamp;
            if ($stamp) {
                $form['stamp_head'] = $stamp->company_name;
                $form['stamp_licence'] = $stamp->licence;
            }

            $validity = UserEdsTrait::getValidityString($user->validity_eds_start, $user->validity_eds_end);
            if ($validity) {
                $form['validity'] = $validity;
            }

            $form['sleep_status'] = $request->sleep_status;
            $form['people_status'] = $request->people_status;
            $form['user_name'] = $user->name;
            $form['driver_fio'] = $driver->fio;

            if ($form['admitted'] === 'Не допущен') {
                $form['reasons'] = $notAdmittedReasons;
            }

            //TODO: вынести в мидлвар или ивент позже
            if ($request->has('logs')) {
                Log::channel('sdpo')->info(json_encode(
                    [
                        'id' => $form->id,
                        'request' => $request->all(),
                        'ip' => $request->getClientIp() ?? null
                    ]
                ));
            }

            $wishMessages = config('wishes.messages');
            $form['wish_message'] = $wishMessages[array_rand($wishMessages)];

            DB::commit();

            return response()->json($form);
        } catch (Throwable $exception) {
            DB::rollBack();

            $code = $exception->getCode();
            if ($code < 400 || $code >= 600) {
                $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            return response()->json([
                'message' => $exception->getMessage()
            ], $code);
        }
    }

    /**
     * @deprecated
     * Check connection sdpo
     */
    public function checkConnection(): JsonResponse
    {
        return response()->json(true);
    }

    /*
     * Get PV name by user
     */
    public function getPoint(Request $request)
    {
        $user = $request->user('api');

        return response()->json($user->pv->name);
    }

    public function getStamp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $stamp = $user->stamp;

        $data = [
            'stamp_head' => null,
            'stamp_licence' => null
        ];

        if ($user->stamp) {
            $data = [
                'stamp_head' => $stamp->company_name,
                'stamp_licence' => $stamp->licence
            ];
        }

        return response()->json($data);
    }

    public function getTerminalVerification(Request $request)
    {
        $user = $request->user('api');

        if (!$user->terminalCheck) {
            return response()->json([
                'serial_number' => null,
                'date_check' => null,
            ]);
        }

        return response()->json([
            'serial_number' => $user->terminalCheck->serial_number,
            'date_check' => $user->terminalCheck->date_check->format('Y-m-d'),
        ]);
    }

    /*
     * return all medics
     */
    public function getMedics(): JsonResponse
    {
        $users = User::query()
            ->with([
                'roles',
                'pv:id,name,pv_id',
                'pv.town:id,name'
            ])
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', 2);
            })
            ->select([
                'id',
                'name',
                'eds',
                'pv_id',
                'validity_eds_start',
                'validity_eds_end'
            ])
            ->get()
            ->groupBy([
                'pv.town.name',
                'pv.name'
            ]);

        return response()->json($users);
    }

    public function getStamps(): JsonResponse
    {
        $stamps = Stamp::query()
            ->select([
                'id',
                'company_name as stamp_head',
                'licence as stamp_licence'
            ])
            ->get()
            ->groupBy('id');

        return response()->json($stamps);
    }

    /*
    * return driver by id
    */
    public function getDriver(Request $request, $id): JsonResponse
    {
        $apiClient = $request->user('api');

        if ($apiClient->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], 400);
        }

        $driver = Driver::where('hash_id', $id)
            ->with('company')
            ->select([
                'hash_id',
                'fio',
                'dismissed',
                'company_id',
                'end_of_ban',
                'photo',
                'phone',
                'only_offline_medic_inspections'
            ])
            ->first();

        if (!$driver) {
            return response()->json(['message' => 'Водитель с указанным ID не найден!'], 400);
        }

        date_default_timezone_set('UTC');
        $time = time();
        $timezone = $apiClient->timezone ?? Auth::user()->timezone;
        $time += $timezone * 3600;
        $time = date('Y-m-d H:i:s', $time);

        if ($driver->end_of_ban && (Carbon::parse($time) < Carbon::parse($driver->end_of_ban))) {
            return response()->json(
                ['message' => 'Указанный водитель отстранен до ' . Carbon::parse($driver->end_of_ban) . "!"],
                400
            );
        }

        if ($driver->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_BLOCK)], 303);
        }

        if ($driver->company->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::COMPANY_BLOCK)], 303);
        }

        if ($driver->only_offline_medic_inspections) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_OFFLINE_ONLY)], 400);
        }

        return response()->json($driver);
    }

    /*
    * return car by id
    */
    public function getCar(Request $request, $id): JsonResponse
    {
        $apiClient = $request->user('api');

        if ($apiClient->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], 400);
        }

        $car = Car::where('hash_id', $id)
            ->with('company')
            ->select([
                'hash_id',
                'gos_number',
                'dismissed',
                'company_id',
            ])
            ->first();

        if (!$car) {
            return response()->json(['message' => 'Авто с указанным ID не найдено!'], 400);
        }

        if ($car->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::CAR_BLOCK)], 303);
        }

        if ($car->company->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::COMPANY_BLOCK)], 303);
        }

        return response()->json($car);
    }

    public function setDriverPhone(Request $request, $id): JsonResponse
    {
        $apiClient = $request->user('api');

        if ($apiClient->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], 400);
        }

        /** @var Driver $driver */
        $driver = Driver::query()
            ->with(['company'])
            ->where('hash_id', $id)
            ->first();

        if (!$driver) {
            return response()->json(['message' => 'Водитель с указанным ID не найден!'], 400);
        }

        if ($driver->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_BLOCK)], 303);
        }

        if ($driver->company->dismissed === 'Да') {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::COMPANY_BLOCK)], 303);
        }

        if ($driver->only_offline_medic_inspections) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::DRIVER_OFFLINE_ONLY)], 400);
        }

        $phone = new Phone($request->input('phone'));
        if (!$phone->isValid()) {
            return response()->json(['message' => 'Некорректный номер телефона!', 422]);
        }

        $driver->setAttribute('phone', $phone->getSanitized());
        $driver->save();

        return response()->json([
            'message' => 'Номер телефона водителя успешно обновлен!'
        ]);
    }

    /*
    * return all drivers
    */
    public function getDrivers(): JsonResponse
    {
        $drivers = Driver::select('hash_id', 'fio')->get();

        return response()->json($drivers);
    }

    /*
    * Return inspection info
    */
    public function getInspection($id): JsonResponse
    {
        $inspection = Form::find($id);
        $details = $inspection->details;

        $data = $inspection->toArray() + $details->toArray();

        $stamp = optional(optional($details->terminal))->stamp;
        if ($stamp) {
            $data['stamp_head'] = $stamp->company_name;
            $data['stamp_licence'] = $stamp->licence;
        }

        $validity = UserEdsTrait::getValidityString(
            $inspection['user_validity_eds_start'] ?? null,
            $inspection['user_validity_eds_end'] ?? null
        );
        if ($validity) {
            $data['validity'] = $validity;
        }

        if ($inspection->user) {
            $data['user_name'] = $inspection->user->name;
        }

        if ($inspection->driver) {
            $data['driver_fio'] = $inspection->driver->fio;
        }

        return response()->json($data);
    }

    /*
    * Inspection update type
    */
    /**
     * @throws Throwable
     */
    public function changeType($id, ChangeSdpoMedicFormTypeHandler $handler)
    {
        try {
            DB::beginTransaction();

            $handler->handle($id, Auth::user());

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
        }
    }

    /*
    * Driver update photo
    */
    public function setDriverPhoto(Request $request, $id)
    {
        if (!$request->photo) {
            return;
        }

        $driver = Driver::where('hash_id', $id)->first();
        if (!$driver) {
            return;
        }

        $image = base64_decode($request->photo);
        $path = "elements/driver_photo_" . $id . ".png";
        Storage::disk('public')->put($path, $image);

        $driver->update([
            'photo' => $path
        ]);
    }

    public function storeCrash(StoreSdpoCrashRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        if ($user->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], 400);
        }

        try {
            DB::beginTransaction();

            SdpoCrashLog::create(
                $request->all() +
                [
                    'terminal_id' => $user->getAttribute('id'),
                    'point_id' => $user->getAttribute('pv_id')
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Ошибка успешно передана на сервер!'
            ]);
        } catch (Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => "Ошибка не была передана на сервер! " . $exception->getMessage()
            ]);
        }
    }

    public function storeFormFeedback(
        StoreSdpoFormFeedbackRequest $request,
        string $id,
        StoreFormEventHandler $handler
    )
    {
        /** @var User $user */
        $user = $request->user('api');

        if ($user->blocked) {
            return response()->json(['message' => BlockActionReasonsEnum::getLabel(BlockActionReasonsEnum::TERMINAL_BLOCK)], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            $formFeedback = FormFeedback::fromItems($request->input('feedback'));

            $handler->handle(new StoreFormEventCommand(
                $id,
                $formFeedback->toArray(),
                $user->id
            ));

            DB::commit();

            $wishMessages = config('wishes.messages');

            return response()
                ->json(['wish_message' => $wishMessages[array_rand($wishMessages)]])
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (NotFoundHttpException $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAnketLabelingQr($id, ExportFormsLabelingPdfHandler $handler)
    {
        try {
            return $handler->handle(new ExportFormsLabelingPdfCommand([$id]));
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRandomWish()
    {
        $wishMessages = config('wishes.messages');
        return response()
            ->json(['wish_message' => $wishMessages[array_rand($wishMessages)]]);
    }
}
