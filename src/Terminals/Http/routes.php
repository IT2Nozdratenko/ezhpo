<?php
declare(strict_types=1);


use Illuminate\Support\Facades\Route;
use Src\Terminals\Http\Controllers\SyncTerminalSettingsController;
use Src\Terminals\Http\Controllers\SyncTerminalSettingsPageController;

Route::middleware(['web', 'auth'])->prefix('/terminals')->as('terminals.')->group(function () {
    Route::get('/sync-settings', SyncTerminalSettingsPageController::class)
        ->name('sync-settings-page');
//        ->middleware('can:pak_sdpo_create');
    Route::post('/sync-settings', SyncTerminalSettingsController::class)
        ->name('sync-settings');
});
