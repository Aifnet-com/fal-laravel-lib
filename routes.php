<?php

use Illuminate\Support\Facades\Route;
use Aifnet\Fal\Http\Controllers\FalWebhookController;
use Aifnet\Fal\Http\Controllers\FalController;

Route::post('/fal/webhook', [FalWebhookController::class, 'handle'])->name('fal.webhook');
Route::get('/fal/download/{falRequestId}', [FalController::class, 'download'])->name('fal.download');