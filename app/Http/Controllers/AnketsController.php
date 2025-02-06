<?php

namespace App\Http\Controllers;

use App\Actions\Anketa\ChangeResultDopHandler;
use App\Actions\Anketa\CreateFormHandlerFactory;
use App\Actions\Anketa\CreateSdpoFormHandler;
use App\Actions\Anketa\ExportFormsLabelingPdf\ExportFormsLabelingPdfCommand;
use App\Actions\Anketa\ExportFormsLabelingPdf\ExportFormsLabelingPdfHandler;
use App\Actions\Anketa\GetFormVerificationDetails\GetFormVerificationDetailsParams;
use App\Actions\Anketa\GetFormVerificationDetails\GetFormVerificationDetailsQuery;
use App\Actions\Anketa\GetFormVerificationHistory\GetFormVerificationHistoryParams;
use App\Actions\Anketa\GetFormVerificationHistory\GetFormVerificationHistoryQuery;
use App\Actions\Anketa\StoreFormVerification\StoreFormVerificationCommand;
use App\Actions\Anketa\StoreFormVerification\StoreFormVerificationHandler;
use App\Actions\Anketa\TrashFormHandler;
use App\Actions\Anketa\UpdateFormHandler;
use App\Actions\PakQueue\ChangePakQueue\ChangePakQueueAction;
use App\Actions\PakQueue\ChangePakQueue\ChangePakQueueHandler;
use App\Enums\FormTypeEnum;
use App\Enums\QRCodeLinkParameter;
use App\Exceptions\ExpiredFormPeriodPlException;
use App\Models\Forms\ActionsPolicy\Builders\BuildersFactory;
use App\Models\Forms\ActionsPolicy\Policies\DisabledPolicy;
use App\Models\Forms\Form;
use App\Models\Forms\MedicForm;
use App\Point;
use App\Services\TripTicketExporter\ViewModels\StampViewModel;
use App\Traits\UserEdsTrait;
use App\User;
use App\ValueObjects\ClientHash;
use App\ValueObjects\NotAdmittedReasons;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Http\Client\Common\Exception\HttpClientNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AnketsController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $type = $request->get('type');
        if (!$type) {
            if ($user->hasRole('manager') || $user->hasRole('engineer_bdd')) {
                return redirect()->route('renderElements', 'Company');
            }
            if ($user->hasRole('operator_sdpo')) {
                return redirect()->route('home', ['type_ankets' => FormTypeEnum::PAK_QUEUE]);
            }
            if ($user->hasRole('client')) {
                return redirect()->route('home', ['type_ankets' => FormTypeEnum::MEDIC]);
            }
            if ($user->hasRole('tech')) {
                $type = FormTypeEnum::TECH;
            }
            if ($user->hasRole('medic')) {
                $type = FormTypeEnum::TECH;
            }
            if (!$type) {
                return redirect()->route('index');
            }
        }

        $forms = [
            'medic' => [
                'title' => 'Медицинский осмотр',
                'anketa_view' => 'profile.ankets.medic',
            ],
            'tech' => [
                'title' => 'Технический осмотр',
                'anketa_view' => 'profile.ankets.tech',
            ],
            'pechat_pl' => [
                'title' => 'Журнал печати путевых листов',
                'anketa_view' => 'profile.ankets.pechat_pl',
            ],
            'pak' => [
                'title' => 'СДПО',
                'anketa_view' => 'profile.ankets.pak',
            ],
            'pak_queue' => [
                'title' => 'Очередь на утверждение',
                'anketa_view' => 'profile.ankets.pak_queue',
            ],
            'bdd' => [
                'title' => 'Журнал инструктажей по БДД',
                'anketa_view' => 'profile.ankets.bdd',
            ],
            'report_cart' => [
                'title' => 'Журнал снятия отчетов с карт',
                'anketa_view' => 'profile.ankets.report_cart',
            ],
        ];

        // Отображаем данные
        $data = $forms[$type];

        // Конвертация текущего времени Юзера
        date_default_timezone_set('UTC');
        $time = time();
        $timezone = $user->timezone ?: 3;
        $time += $timezone * 3600;
        $time = date('Y-m-d\TH:i', $time);

        // Дефолтные значения
        $data['default_current_date'] = $time;
        $data['points'] = Point::getAll();
        $data['type_anketa'] = $type;
        $data['default_pv_id'] = $user->pv_id;
        $data['car_id'] = $request->input(QRCodeLinkParameter::CAR_ID);
        $data['driver_id'] = $request->input(QRCodeLinkParameter::DRIVER_ID);

        // Проверяем выставленный ПВ
        if (session()->exists('anketa_pv_id') && ((date('d.m') > session('anketa_pv_id')['expired']))) {
            session()->remove('anketa_pv_id');
        }

        $data['actions_policy'] = new DisabledPolicy();

        return view('profile.anketa', $data);
    }

    public function Get(Request $request, BuildersFactory $buildersFactory)
    {
        /** @var Form $form */
        $form = Form::withTrashed()->findOrFail($request->id);
        $details = $form->details;

        $data = array_merge($form->toArray(), $details->toArray());

        $companyFields = config('elements')['Driver']['fields']['company_id'];
        $companyFields['getFieldKey'] = 'name';

        $data['title'] = 'Редактирование осмотра';

        if ($form->date) {
            $data['default_current_date'] = date('Y-m-d\TH:i', strtotime($form->date));
        }

        $data['points'] = Point::getAll();
        $data['anketa_view'] = 'profile.ankets.' . $form->type_anketa;
        $data['pv_id'] = $form->point_id ?? $form->pv_id;
        $data['anketa_route'] = 'forms.update';
        $data['company_fields'] = $companyFields;

        if ($form->type_anketa === FormTypeEnum::PAK_QUEUE) {
            /** @var MedicForm $details */
            $data['not_admitted_reasons'] = NotAdmittedReasons::fromForm($details)->getReasons();
        }

        $data['actions_policy'] = $buildersFactory->make()->build($form, Auth::user());

        return view('profile.anketa', $data)->with('errors', $request->get('errors', []));
    }

    public function Trash(Request $request, TrashFormHandler $handler)
    {
        $id = $request->id;
        $action = $request->action;
        $form = Form::withTrashed()->findOrFail($id);

        try {
            DB::beginTransaction();

            $handler->handle($form, $action, Auth::user());

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            session()->flash('not_deleted_ankets', [$id]);
        }

        return redirect(url()->previous());
    }

    public function MassTrash(Request $request, TrashFormHandler $handler): JsonResponse
    {
        $ids = $request->input('ids') ?? [];
        $action = $request->input('action');
        $notDeletedForms = [];

        foreach ($ids as $id) {
            try {
                DB::beginTransaction();

                $form = Form::withTrashed()->findOrFail($id);

                $handler->handle($form, $action, Auth::user());

                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();

                $notDeletedForms[] = $id;
            }
        }

        if (count($notDeletedForms)) {
            session()->flash('not_deleted_ankets', $notDeletedForms);
        }

        return response()->json();
    }

    public function ChangePakQueue(Request $request, $id, $admitted, ChangePakQueueHandler $handler)
    {
        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = Auth::user();
            $handler->handle(new ChangePakQueueAction($id, $admitted, $user));

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(
                    ['message' => 'Осмотр успешно принят']
                );
            } else {
                return back();
            }
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(
                    ['message' => $exception->getMessage()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            } else {
                return back()->with('error', $exception->getMessage());
            }
        }
    }

    public function ChangeResultDop($id, $result_dop, ChangeResultDopHandler $handler): RedirectResponse
    {
        $form = Form::withTrashed()->findOrFail($id);

        try {
            DB::beginTransaction();

            $handler->handle($form, $result_dop);

            DB::commit();

            return back();
        } catch (Throwable $exception) {
            DB::rollBack();

            return back()->with('error', $exception->getMessage());
        }
    }

    public function ChangeMultipleResultDop(Request $request, ChangeResultDopHandler $handler): JsonResponse
    {
        $ids = $request->input('ids', []);
        $result = $request->input('result', 'Утвержден');

        $errors = [];

        foreach ($ids as $id) {
            $form = Form::withTrashed()->find($id);

            if ($form === null) {
                $errors[] = "Осмотр с id $id не найден";
                continue;
            }

            try {
                DB::beginTransaction();

                $handler->handle($form, $result);

                DB::commit();
            } catch (Throwable $exception) {
                DB::rollBack();

                $errors[] = $exception->getMessage();
            }
        }

        if (count($errors)) {
            session()->flash('mass_approve_errors', $errors);
        }

        return response()->json();
    }

    public function Update(Request $request, UpdateFormHandler $handler): RedirectResponse
    {
        DB::beginTransaction();

        $id = $request->id;

        $form = Form::withTrashed()->findOrFail($id);

        try {

            $handler->handle($form, $request->all(), Auth::user());

            $referer = $request->input('REFERER');
            if ($referer) {
                $response = redirect($referer);
            } else {
                $response = redirect(route('forms.get', [
                    'id' => $id,
                    'msg' => 'Осмотр успешно обновлён!'
                ]));
            }

            DB::commit();
        } catch (Throwable $exception) {
            $response = redirect(route('forms.get', [
                'id' => $id,
                'errors' => [$exception->getMessage()],
            ]));

            DB::rollBack();
        }

        return $response;
    }

    public function AddForm(Request $request, CreateFormHandlerFactory $factory): RedirectResponse
    {
        DB::beginTransaction();

        $formType = $request->input('type_anketa');

        $responseData = [];

        try {
            // TODO: добавить время действия
            session(['anketa_pv_id' => [
                'value' => $request->get('pv_id', 0),
                'expired' => date('d.m')
            ]]);

            $handler = $factory->make($formType);

            $responseData = $handler->handle($request->all(), Auth::user());

            DB::commit();
        } catch (Throwable $exception) {
            $responseData['errors'] = [$exception->getMessage()];

            DB::rollBack();
        }

        $responseData['type'] = $formType;
        $responseData['is_dop'] = $responseData['is_dop'] ?? $request->input('is_dop', 0);

        return back()->with($responseData);
    }

    /**
     * @deprecated
     * API ROUTE FOR SDPO
     */
    public function ApiAddForm(Request $request, CreateSdpoFormHandler $handler): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
                $photosPaths = [];

                foreach ($photos as $photo) {
                    $photosPaths[] = Storage::disk('public')
                        ->putFile('ankets', $photo);
                }

                $data['photos'] = implode(',', $photosPaths);
            }

            $responseData = $handler->handle($data, $request->user('api'));

            DB::commit();

            Log::channel('deprecated-api')->info(json_encode(
                [
                    'request' => $request->all(),
                    'ip' => $request->getClientIp() ?? null,
                    'response' => $responseData
                ]
            ));

            return response()->json(response()->json($responseData));
        } catch (Throwable $exception) {
            DB::rollBack();

            $responseData = [
                'errors' => [$exception->getMessage()],
            ];

            Log::channel('deprecated-api')->info(json_encode(
                [
                    'request' => $request->all(),
                    'ip' => $request->getClientIp() ?? null,
                    'response' => $responseData
                ]
            ));

            return response()->json(response()->json($responseData), 500);
        }
    }

    public function print($id)
    {
        $form = Form::withTrashed()->findOrFail($id);
        /** @var MedicForm $details */
        $details = $form->details;

        $pdf = Pdf::loadView('docs.print', [
            'form' => $form,
            'stamp' => StampViewModel::fromStampOrDefault($details->getStamp()),
            'user' => User::find($form->user_id),
            'validity' => UserEdsTrait::getValidityString(
                $form->user_validity_eds_start,
                $form->user_validity_eds_end
            )
        ]);

        $response = response()->make($pdf->output(), 200);
        $response->header('Content-Type', 'application/pdf');

        return $response;
    }

    public function exportPdfLabeling(Request $request, ExportFormsLabelingPdfHandler $handler)
    {
        $anketIds = $request->input('anket_ids');

        if (count($anketIds) > 40) {
            return response()->json()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            return $handler->handle(new ExportFormsLabelingPdfCommand($anketIds));
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verificationPage(string $uuid, GetFormVerificationDetailsQuery $query)
    {
        $user = Auth::user();

        $userId = null;
        if ($user) {
            $userId = $user->id;
        }

        try {
            $details = $query->get(new GetFormVerificationDetailsParams(
                $uuid,
                $userId
            ));

            return view('pages.form-verification.show', [
                'details' => $details,
            ]);
        } catch (HttpClientNotFoundException|ExpiredFormPeriodPlException $exception) {
            return view('pages.form-verification.404');
        } catch (Throwable $exception) {
            return view('pages.form-verification.500', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function verificationHistory(
        string                          $uuid,
        Request                         $request,
        GetFormVerificationHistoryQuery $getVerificationHistoryQuery,
        StoreFormVerificationHandler    $createVerificationHandler

    ): JsonResponse
    {
        $clientHash = $request->input("client_hash");
        $date = $request->input("date");

        if (!$clientHash) {
            $clientHash = ClientHash::from($request->ip(), $request->header('User-Agent'))->value();
        }

        try {
            $createVerificationHandler->handle(new StoreFormVerificationCommand(
                $uuid,
                $clientHash,
                Auth::check(),
                Carbon::parse($date)
            ));

            $historyItems = $getVerificationHistoryQuery->get(new GetFormVerificationHistoryParams(
                $uuid,
                $clientHash
            ));

            return response()
                ->json([
                    'items' => $historyItems,
                    'clientHash' => $clientHash,
                ])
                ->setStatusCode(Response::HTTP_OK);
        } catch (HttpClientNotFoundException $exception) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
