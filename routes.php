<?php

use Illuminate\Support\Facades\Route;
use Aifnet\Fal\Http\Controllers\FalWebhookController;

Route::post('/fal/webhook', [FalWebhookController::class, 'handle'])->name('fal.webhook');
