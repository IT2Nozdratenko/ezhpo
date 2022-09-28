<?php

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/fixRoles', function() {

});

// Маршруты статичных и главных страниц
Route::get('/', 'IndexController@RenderIndex')->name('index');
Route::get('/releases', 'IndexController@RenderReleases')->name('releases');


Route::get('/show-video', function () {
    $url = isset($_GET['url']) ? $_GET['url'] : '';

    return view('showVideo', [
        'video' => $url
    ]);
})->name('showVideo');

Route::get('/show-edit-element-modal/{model}/{id}', 'IndexController@ShowEditModal')->name('showEditElementModal');

/**
 * API-маршруты
 */

// Сброс пункта выпуска
Route::get('/api/pv-reset/$2y$10$I.RBe8HbmRj2xwpRFWl15OHmWRIMz98RXy1axcK8Jrnx', 'ApiController@ResetAllPV')->name('api.resetpv');
Route::get('/api/getField/{model}/{field}/{default_value?}', 'IndexController@GetFieldHTML');

Route::prefix('snippet')->group(function () {
    Route::get('/update-pak-fields/$2y$10$I.RBe8HbmRj2xwpRFWl15OHmWRIMz98RXy1axcK8Jrnx', function () {
        $ankets = \App\Anketa::where('is_pak', 1)
            ->where('type_anketa', 'medic')
            ->update(array( 'flag_pak' => 'СДПО А' ));

        return response()->json($ankets);
    });

    Route::get('/driver-to-user-all/$2y$10$I.RBe8HbmRj2xwpRFWl15OHmWRIMz98RXy1axcK8Jrnx', function () {
        $drivers = DB::select('select * from drivers WHERE hash_id NOT IN (SELECT login FROM users)');

        if(count($drivers) <= 0) {
            echo 'все водители добавлены как пользователи';
        }

        foreach($drivers as $driver) {
            $pv_id = isset($driver->company_id) ? \App\Company::where('id', $driver->company_id)->first() : 0;

            if($pv_id) {
                $pv_id = $pv_id->pv_id ?? 0;
            }

            if(!$pv_id) {
                $pv_id = 0;
            }

            $register = new \App\Http\Controllers\Auth\RegisterController();
            $created = $register->create([
                'hash_id' => $driver->hash_id . rand(999,99999),
                'email' => $driver->hash_id . 'driver@ta-7.ru',
                'login' => $driver->hash_id,
                'password' => $driver->hash_id,
                'name' => $driver->fio,
                'pv_id' => $pv_id,
                'role' => 3
            ]);

            echo '['.$driver->hash_id.'] Создан Водитель Как Пользователь ' . '  ('.count($drivers).')<br/>';
        }
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('driver-dashboard', function () {
        return view('pages.driver');
    })->name('page.driver');

    Route::get('/add-client', 'IndexController@RenderAddClient')->name('pages.add_client');

    Route::get('driver-bdd', function () {
        $instrs = \App\Instr::where('active', 1)->orderBy('sort', 'asc')->get();
        $pv_id = \App\Driver::where('hash_id', auth()->user()->id)->first();

        if($pv_id) {
            $pv_id = \App\Company::find($pv_id->company_id);

            if($pv_id) {
                $pv_id = $pv_id->pv_id;
            } else {
                $pv_id = 0;
            }
        } else {
            $pv_id = 0;
        }

        return view('pages.driver_bdd', [
            'instrs' => $instrs,
            'pv_id' => $pv_id
        ]);
    })->name('page.driver_bdd');

    Route::prefix('profile')->group(function () {
        Route::post('/anketa', 'AnketsController@AddForm')->name('addAnket');

        Route::get('delete-avatar', 'ProfileController@DeleteAvatar')->name('deleteAvatar');
        Route::get('/', 'ProfileController@RenderIndex')->name('profile');
        Route::post('/', 'ProfileController@UpdateData')->name('updateProfile');
    });
});


Route::middleware(['auth'])->group(function () {
    // Users && Groups || permissions
    Route::get('/users', 'UserController@index')->name('users');
    Route::get('/users/fetchUserData', 'UserController@fetchUserData');
    Route::get('/users/saveUser', 'UserController@saveUser');
    Route::post('/users', 'UserController@destroy');
    Route::get('users/fetchRoleData', 'UserController@fetchRoleData');
    Route::post('users/return_trash', 'UserController@returnTrash');
    Route::get('users/fetchCompanies', 'UserController@fetchCompanies');
    //Route::get('/users/index', 'UserController@index')->name('users');

    Route::resource('roles', 'RoleController');
    Route::post('roles/return_trash', 'RoleController@returnTrash');

    Route::any('/field/prompt/filter', 'FieldPromptController@getAll');
    Route::resource('field/prompt', 'FieldPromptController');
});



/**
 * Профиль, анкета, авторзация
 */
Route::middleware(['auth', \App\Http\Middleware\CheckDriver::class])->group(function () {
    Route::get('/home/filters', 'HomeController@getFilters');
    Route::get('/home/{type_ankets?}/filters', 'HomeController@getFilters')->name('home.filters');
    Route::get('/home/{type_ankets?}', 'HomeController@index')->name('home');

    Route::prefix('profile')->group(function () {
        Route::get('/anketa', 'IndexController@RenderForms')->name('forms');
    });

    Route::prefix('docs')->group(function () {
        Route::get('{type}/{anketa_id}', 'DocsController@Get')->name('docs.get');
    });

    // Рендер элемента (водитель, компания и т.д.)
    Route::get('/elements/{type}', 'IndexController@RenderElements')->name('renderElements');
});


/**
 * Элементы CRM
 */
Route::middleware(['auth', \App\Http\Middleware\CheckDriver::class])->group(function () {
    Route::prefix('elements')->group(function () {
        // Удаление элемента (водитель, компания и т.д.)
        Route::get('/{type}/{id}', 'IndexController@RemoveElement')->name('removeElement');
        // Добавление элемента (водитель, компания и т.д.)
        Route::post('/{type}', 'IndexController@AddElement')->name('addElement');
        // Обновление элемента (водитель, компания и т.д.)
        Route::post('/{type}/{id}', 'IndexController@updateElement')->name('updateElement');
        // Синхронизация для компаний
        Route::get('/{type}/sync/{id}', 'IndexController@syncElement')->name('syncElement');

        // Удаление файла
        Route::get('/delete-file/{model}/{id}/{field}', 'IndexController@DeleteFileElement')->name('deleteFileElement');
    });

    Route::post('/elements-import/{type}', 'IndexController@ImportElements')->name('importElements');
    Route::get('/elements-syncdata/{fieldFindId}/{fieldFind}/{model}/{fieldSync}/{fieldSyncValue?}', 'IndexController@SyncDataElement')->name('syncDataElement');

    Route::prefix('anketa')->group(function () {
        Route::delete('/{id}', 'AnketsController@Delete')->name('forms.delete');
        Route::post('/{id}', 'AnketsController@Update')->name('forms.update');
        Route::get('/{id}', 'AnketsController@Get')->name('forms.get');

        Route::get('/change-pak-queue/{id}/{admitted}', 'AnketsController@ChangePakQueue')->name('changePakQueue');
        Route::get('/change-resultdop-queue/{id}/{result_dop}', 'AnketsController@ChangeResultDop')->name('changeResultDop');
    });

    Route::prefix('report')->group(function () {
        Route::get('journal', 'ReportController@ShowJournal')->name('report.journal');
        Route::get('{type_report}', 'ReportController@GetReport')->name('report.get');
    });

    // Сохранение полей в HOME
    Route::post('/save-fields-home/{type_ankets}', 'HomeController@SaveCheckedFieldsFilter')->name('home.save-fields');

    Route::get('/anketa-trash/{id}/{action}', 'AnketsController@Trash')->name('forms.trash');
});

/**
 * Панель администратора
 */
Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->group(function () {

        // Модернизация пользователей
        Route::get('/users', 'AdminController@ShowUsers')->name('adminUsers');
        Route::post('/users', 'AdminController@CreateUser')->name('adminCreateUser');
        Route::get('/users/{id}', 'AdminController@DeleteUser')->name('adminDeleteUser');
        Route::post('/users/{id}', 'AdminController@UpdateUser')->name('adminUpdateUser');

        Route::prefix('settings')->group(function () {
            Route::get('/', 'SettingsController@index')->name('settings.index');
            Route::post('/', 'SettingsController@update')->name('settings.update');
        });

    });

});

// Маршруты авторизации
Auth::routes();

