<?php

use App\Http\Middleware\CheckDriver;
use App\Http\Middleware\StripEmptyParamsFromQueryString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('show-video', 'IndexController@showVideo')->name('showVideo');

Route::middleware(['auth'])->group(function () {
    Route::post('show-edit-element-modal/{model}/{id}', 'IndexController@showEditModal')->name('showEditElementModal');

    Route::get('/', 'IndexController@index')->name('index');
    Route::get('/openapi', 'OpenApiUiPageController@index')->name('index');

    Route::prefix('contract')->group(function () {
        Route::get('/', 'ContractController@view');
        Route::put('restore/{id}', 'ContractController@restore');
        Route::get('index', 'ContractController@index');
        Route::post('update', 'ContractController@update');
        Route::get('create', 'ContractController@create');
        Route::delete('{id}', 'ContractController@destroy');
        Route::get('getTypes', 'ContractController@getTypes');
        Route::post('getCarsByCompany/{id}', 'ContractController@getCarsByCompany');
        Route::post('getDriversByCompany/{id}', 'ContractController@getDriversByCompany');
        Route::post('getAvailableForCompany', 'ContractController@getAvailableForCompany');

        Route::prefix('select')->group(function () {
            Route::get('companies', 'ContractSelectsController@companies');
            Route::get('cars', 'ContractSelectsController@cars');
            Route::get('drivers', 'ContractSelectsController@drivers');
            Route::get('products', 'ContractSelectsController@products');
            Route::get('our_companies', 'ContractSelectsController@ourCompanies');
        });
    });

    Route::get('add-client', 'IndexController@RenderAddClient')->name('pages.add_client');

    Route::get('driver-dashboard', 'DriverController@index')->name('driver.index');
    Route::prefix('driver-bdd')->as('driver.bdd.')->group(function () {
        Route::get('/', 'BddController@get')->name('index');
        Route::post('/', 'BddController@store')->name('store');
    });

    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('delete-avatar', 'ProfileController@deleteAvatar')->name('deleteAvatar');
        Route::get('/', 'ProfileController@index')->name('index');
        Route::post('/', 'ProfileController@updateAvatar')->name('updateAvatar');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', 'UserController@index')->name('users');
        Route::post('/', 'UserController@destroy');
        Route::get('fetchCompanies', 'UserController@fetchCompanies');
        Route::get('fetchRoleData', 'UserController@fetchRoleData');
        Route::get('fetchUserData', 'UserController@fetchUserData');
        Route::post('return_trash', 'UserController@returnTrash');
        Route::post('saveUser', 'UserController@saveUser');
    });

    Route::prefix('employees')->as('employees.')->middleware('auth')->group(function () {
        Route::get('/', 'Employees\IndexEmployeesPageController')->name('index');
        Route::post('/', 'Employees\CreateEmployeeController')->name('create');
        Route::get('/table-items', 'Employees\GetEmployeesTableItemsController')->name('table-items');
        Route::get('/{id}', 'Employees\GetEmployeeItemController')->name('item');
        Route::delete('/{id}', 'Employees\DeleteEmployeeController')->name('delete');
        Route::put('/{id}', 'Employees\UpdateEmployeeController')->name('update');
        Route::post('/{id}/restore', 'Employees\RestoreEmployeeController')->name('restore');
        Route::post('/permissions-by-roles', 'Employees\GetPermissionsByRolesController')->name('permissions-by-roles');
    });

    Route::prefix('terminals')->as('terminals.')->group(function () {
        Route::get('/', 'TerminalController@index')->name('index');
        Route::post('/', 'TerminalController@update')->name('update');
        Route::get('status', 'TerminalController@getConnectionStatus')->name('status');
        Route::get('to-check', 'TerminalController@terminalsToCheck')->name('to-check');
    });

    Route::prefix('terminals/v2')->as('terminals.v2.')->group(function () {
        Route::get('/', 'Terminals\IndexTerminalsPageController')->name('index');
        Route::get('/table-items', 'Terminals\GetTerminalsTableItemsController')->name('table-items');
        Route::get('to-check', 'Terminals\GetTerminalsToCheckController')->name('to-check');
        Route::get('/{id}', 'Terminals\GetTerminalItemController')->name('item');
        Route::post('/', 'Terminals\CreateTerminalController')->name('store');
        Route::put('/{id}', 'Terminals\UpdateTerminalController')->name('update');
        Route::delete('/{id}', 'Terminals\DeleteTerminalController')->name('delete');
        Route::post('/status', 'Terminals\GetTerminalsConnectionStatusController')->name('status');
    });

    Route::resource('roles', 'RoleController');
    Route::prefix('roles')->as('roles.')->group(function () {
        Route::post('return_trash', 'RoleController@returnTrash');
    });

    Route::resource('field/prompt', 'FieldPromptController')->except(['edit', 'show', 'store', 'create']);
    Route::prefix('field/prompt')->as('prompt.')->group(function () {
        Route::any('filter', 'FieldPromptController@getAll');
    });

    Route::resource('stamp', 'StampController')->except(['show', 'create', 'edit']);
    Route::prefix('stamp')->as('stamp.')->group(function () {
        Route::any('filter', 'StampController@getAll');
        Route::any('find', 'StampController@find');
    });

    Route::prefix('agreement')->group(function () {
        Route::get('/', 'IndexController@agreement');
        Route::post('/', 'IndexController@acceptAgreement');
    });

    Route::middleware([CheckDriver::class])->group(function () {
        /**
         * Профиль, анкета, авторзация
         */
        Route::prefix('home')->group(function () {
            Route::get('filters', 'HomeController@getFilters');
            Route::get('{type_ankets?}/filters', 'HomeController@getFilters')->name('home.filters');
            Route::get('pak_queue', 'PakController@index');
            Route::middleware(StripEmptyParamsFromQueryString::class)->get('{type_ankets?}', 'HomeController@index')->name('home');
        });

        Route::prefix('pak')->as('pak.')->group(function () {
            Route::get('/', 'PakController@index')->name('index');
            Route::get('list', 'PakController@list')->name('list');
            Route::get('clear', 'PakController@clear')->name('clear');
        });

        Route::prefix('docs')->as('docs.')->group(function () {
            Route::get('{type}/{anketa_id}/pdf', 'DocsController@getPdf')->name('get.pdf');
            Route::post('{type}/{anketa_id}/set', 'DocsController@setPdf')->name('add.pdf');
            Route::any('{type}/{anketa_id}/delete', 'DocsController@delete')->name('delete');
            Route::get('{type}/{anketa_id}', 'DocsController@Get')->name('get');
        });

        /**
         * Элементы CRM
         */
        Route::prefix('elements')->group(function () {
            Route::post('generate', 'Elements\GenerateMetricController')->name('generateMetric');
            Route::post('/export/{type}', 'Elements\ExportElementController')->name('exportElement');
            Route::post('/import', 'Elements\ImportElementController')->name('importElement');
            Route::post('/search', 'Elements\SearchElementsController')->name('searchElement');
            Route::middleware(StripEmptyParamsFromQueryString::class)->get('{type}', 'IndexController@RenderElements')->name('renderElements');
            Route::get('{type}/{id}', 'IndexController@RemoveElement')->name('removeElement');
            Route::post('{type}', 'IndexController@AddElement')->name('addElement');
            Route::post('{type}/{id}', 'IndexController@updateElement')->name('updateElement');
            Route::get('{type}/sync/{id}', 'IndexController@syncElement')->name('syncElement');
            Route::get('delete-file/{model}/{id}/{field}', 'IndexController@DeleteFileElement')->name('deleteFileElement');
        });

        Route::get('/companies/select', 'ApiController@companiesList')->name('companies.select');

        Route::get('elements-syncdata/{fieldFindId}/{fieldFind}/{model}/{fieldSync}/{fieldSyncValue?}', 'IndexController@SyncDataElement')->name('syncDataElement');

        Route::prefix('forms')->as('forms.')->group(function () {
            Route::get('/', 'AnketsController@index')->name('index');
            Route::post('/', 'AnketsController@AddForm')->name('store');
            Route::post('/change-multiple-result-dop', 'AnketsController@ChangeMultipleResultDop')->name('changeMultipleResultDop');
            Route::get('{id}/print', 'AnketsController@Print')->name('print');
            Route::delete('{id}', 'AnketsController@Delete')->name('delete');
            Route::post('{id}', 'AnketsController@Update')->name('update');
            Route::get('{id}', 'AnketsController@Get')->name('get');
            Route::get('{id}/change-pak-queue/{admitted}', 'AnketsController@ChangePakQueue')->name('changePakQueue');
            Route::get('{id}/change-resultdop-queue/{result_dop}', 'AnketsController@ChangeResultDop')->name('changeResultDop');
        });

        Route::prefix('report')->as('report.')->group(function () {
            Route::get('journal', 'ReportController@index')->name('journal');
            Route::get('{type_report}', 'ReportController@getReport')->name('get');
            Route::get('/dynamic/{journal}', 'ReportController@getDynamic')->name('dynamic');
        });

        Route::post('save-fields-home/{type_ankets}', 'HomeController@SaveCheckedFieldsFilter')->name('home.save-fields');
        Route::get('anketa-trash/{id}/{action}', 'AnketsController@Trash')->name('forms.trash');
        Route::get('anketa-mass-trash', 'AnketsController@MassTrash')->name('forms.mass-trash');

        Route::post('ankets-export-pdf-labeling', 'AnketsController@exportPdfLabeling')->name('ankets.export-pdf-labeling');

        Route::prefix('trip-tickets')->as('trip-tickets.')->group(function () {
            Route::get('/', 'TripTickets\TripTicketIndexPageController')->name('index');
            Route::post('generate', 'TripTickets\TripTicketGenerateFromFormsController')->name('generate');
            Route::get('create', 'TripTickets\TripTicketCreatePage')->name('create');
            Route::post('store', 'TripTickets\StoreTripTicketController')->name('store');
            Route::get('{id}/edit', 'TripTickets\TripTicketEditPageController')->name('edit');
            Route::post('{id}/update', 'TripTickets\UpdateTripTicketController')->name('update');
            Route::get('trash', 'TripTickets\TripTicketTrashController')->name('trash');
            Route::get('mass-trash', 'TripTickets\TripTicketMassTrashController')->name('mass-trash');
            Route::get('{id}/{type}', 'TripTickets\TripTicketCreateFormPageController')->name('create-form');
            Route::post('{id}/store-form', 'TripTickets\TripTicketStoreFormController')->name('store-form');
            Route::post('print', 'TripTickets\PrintTripTicketController')->name('print');
            Route::post('mass-print', 'TripTickets\MassPrintTripTicketsController')->name('mass-print');
            Route::get('table-export', 'TripTickets\TripTicketTableExportController')->name('table-export');
        });
    });

    /**
     * Панель администратора
     */
    Route::prefix('admin')->group(function () {
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('/', 'SettingsController@index')->name('index');
            Route::post('/', 'SettingsController@update')->name('update');
        });

        Route::prefix('logs')->as('logs.')->group(function () {
            Route::get('/', 'LogController@index')->name('index');
            Route::post('list', 'LogController@list')->name('list');
            Route::post('list-model', 'LogController@listByModel')->name('list-model');
            Route::post('list-model-map', 'LogController@listByModelMaps')->name('list-model-map');
        });

        Route::prefix('form-logs')->as('form-logs.')->group(function () {
            Route::get('/', 'FormLogController@index')->name('index');
            Route::get('/get-form/', 'FormLogController@getFrom')->name('get-from');
            Route::post('list', 'FormLogController@list')->name('list');
            Route::post('list-model', 'FormLogController@listByModel')->name('list-model');
            Route::post('list-model-map', 'FormLogController@listByModelMaps')->name('list-model-map');
        });

        Route::prefix('sdpo-crash-logs')->as('sdpo_crash_logs.')->group(function () {
            Route::get('/', 'SdpoCrashLogController@index')->name('index');
            Route::post('list', 'SdpoCrashLogController@list')->name('list');
        });
    });
});

Route::get('/anketa-verification/{uuid}', 'AnketsController@verificationPage')->name('anketa.verification.page');
Route::get('/anketa-verification/{uuid}/history', 'AnketsController@verificationHistory')->name('anketa.verification.history');

Auth::routes();

