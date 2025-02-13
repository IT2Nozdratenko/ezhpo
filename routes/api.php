<?php

use App\Enums\FormTypeEnum;
use App\Models\Forms\Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;
use App\Point;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/companies/find', 'ApiController@companiesList');
Route::get('/find/{model}', 'ApiController@modelList');

Route::middleware('auth:api')->group(function () {
    Route::get('getField/{model}/{field}/{default_value?}', 'IndexController@GetFieldHTML');
    Route::get('parse-qr-code', 'Api\Forms\TechnicalInspection\ParseQRCodeController');

    Route::get('/users/{role}', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('terminal')) {
            $user = User::with('roles')->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', 2);
            })->get();

            return response()->json($user);
        }
    });

    Route::post('/get-user-from-token', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('terminal')) {
            if (isset($request->token)) {
                $user = User::where('api_token', $request->token)->first();
            }

            return response()->json($user);
        }
    });

    Route::post('/get-user/{user_id}', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('terminal')) {
            $user_id = $request->user_id;
            $user = User::find($user_id);

            return response()->json($user);
        }
    });

    /**
     * Роуты для старых версий СДПО
     * @deprecated
     */
    Route::prefix('anketa')->group(function () {
        Route::post('/', 'AnketsController@ApiAddForm');
        Route::get('/{id}', function ($id) {
            $form = Form::find($id);

            if ($form && $form->type_anketa == FormTypeEnum::PAK_QUEUE) {
                if (Carbon::now()->getTimestamp() - Carbon::parse($form->created_at)->getTimestamp() > 12) {
                    $form->type_anketa = FormTypeEnum::MEDIC;
                    $form->save();
                }
            }

            return response()->json($form);
        });
    });

    Route::prefix('reports')->group(function () {
        Route::get('/journal', 'ReportController@getJournalData');
        Route::get('/journal/export', 'ReportController@exportJournalData');
        Route::get('/graph_pv', 'ReportController@getGraphPvData');
    });

    // Отображаем ПВ
    Route::get('pvs/{id?}', function () {
        $id = isset(request()->id) ? request()->id : null;

        if ($id) {
            $points = Point::find($id);
        } else {
            $points = Point::all();
        }

        return response()->json($points);
    });

    Route::get('/check-prop-one/{prop}/{model}/{val}', 'ApiController@OneCheckProperty');
    Route::get('/check-prop/{prop}/{model}/{val}', 'ApiController@CheckProperty');
    Route::post('/get-previous-odometer/', 'ApiController@getPreviousOdometer');

    Route::put('/update-ddate/{item_model}/{item_id}/{item_field}', 'ApiController@UpdateProperty')->name('updateDDate');
    Route::put('/update-doc/{type}', 'DocsController@update')->name('docs.update');

    Route::post('/fields/visible', 'ApiController@saveFieldsVisible');

    Route::prefix('1c/v1')->as('1c.v1.')->group(function () {
        Route::get('/companies', 'Api\OneC\GetCompaniesItemsController');
        Route::get('/requisites', 'Api\OneC\GetRequisitesItemsController');
        Route::post('/companies', 'Api\OneC\CreateCompanyController');
        Route::post('/reports', 'Api\OneC\CreateReportJobController');
        Route::get('/reports/{id}', 'Api\OneC\GetReportController');
    });
});

Route::middleware(['auth:api', 'update-last-connection'])->prefix('sdpo')->name('sdpo')->group(function () {
    Route::post('/get-sticker', 'Api\Forms\TechnicalInspection\QRCodeStickerController');

    Route::prefix('anketa')->group(function () {
        Route::post('/', 'Api\SdpoController@createAnketa');
        Route::post('/{id}', 'Api\SdpoController@changeType');
        Route::get('/{id}', 'Api\SdpoController@getInspection');
        Route::post('/labeling-qr/{id}', 'Api\SdpoController@getAnketLabelingQr');
    });

    Route::get('/drivers', 'Api\SdpoController@getDrivers');
    Route::prefix('driver')->group(function () {
        Route::get('/{id}', 'Api\SdpoController@getDriver');
        Route::get('/{id}/prints', 'Api\SdpoController@getPrints');
        Route::post('/{id}/photo', 'Api\SdpoController@setDriverPhoto');
        Route::post('/{id}/phone', 'Api\SdpoController@setDriverPhone');
    });

    Route::prefix('car')->group(function () {
        Route::get('/{id}', 'Api\SdpoController@getCar');
    });

    Route::prefix('/forms')->group(function () {
        Route::get('/duplicates', 'Api\Forms\CheckInspectionDuplicatesController');
        Route::post('/{id}/feedback', 'Api\SdpoController@storeFormFeedback');
    });

    Route::get('/pv', 'Api\SdpoController@getPoint');
    Route::get('/stamp', 'Api\SdpoController@getStamp');
    Route::get('/stamps', 'Api\SdpoController@getStamps');
    Route::get('/terminal/verification', 'Api\SdpoController@getTerminalVerification');
    Route::get('/medics', 'Api\SdpoController@getMedics');
    Route::get('/settings', 'Api\SdpoController@getSettings');

    Route::post('/crash', 'Api\SdpoController@storeCrash');
    Route::get('wish-message', 'Api\SdpoController@getRandomWish');
});

Route::get('/sdpo/check', 'Api\SdpoController@checkConnection');

