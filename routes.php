<?php

use App\Lib\Fal\Http\Controllers\FalAdminController;
use App\Lib\Fal\Http\Controllers\FalWebhookController;

Route::post('/fal/webhook', [FalWebhookController::class, 'handle'])->name('fal.webhook');

Route::group(['middleware' => 'devAdmin'], function() {
    Route::get('/addmin/fal', [FalAdminController::class, 'index'])->name('fal.admin');
    Route::get('/addmin/fal-dashboard-data', [FalAdminController::class, 'dashboardData'])->name('fal.admin.dashboardData');

    Route::get('/addmin/fal/statistics', [FalAdminController::class, 'statistics'])->name('fal.admin.statistics');
});