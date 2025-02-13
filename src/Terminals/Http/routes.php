<?php
declare(strict_types=1);


use Illuminate\Support\Facades\Route;
use Src\Terminals\Http\Controllers\SyncTerminalSettingsPageController;

Route::middleware(['web', 'auth'])->prefix('/terminals')->as('terminals.')->group(function () {
    Route::get('/sync-settings', SyncTerminalSettingsPageController::class)
        ->name('sync-settings');
//        ->middleware('can:pak_sdpo_create')
});
