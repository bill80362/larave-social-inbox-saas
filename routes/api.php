<?php

use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook/facebook', [WebhookController::class, 'challenge']);

Route::post('/webhook/line', [WebhookController::class, 'handle'])
    ->middleware('webhook.verify.line');

Route::post('/webhook/facebook', [WebhookController::class, 'handle'])
    ->middleware('webhook.verify.facebook');

Route::post('/webhook/instagram', [WebhookController::class, 'handle'])
    ->middleware('webhook.verify.instagram');

Route::post('/webhook/google_business', [WebhookController::class, 'handle'])
    ->middleware('webhook.verify.google');
