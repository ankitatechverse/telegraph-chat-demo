<?php

use Illuminate\Support\Facades\Route;
use DefStudio\Telegraph\Models\TelegraphBot;
use App\Http\Controllers\TelegramWebhookController;

// Home
Route::get('/', function () {
    return view('welcome');
});

// Telegram webhook - MUST BE FIRST!
Route::post('/telegraph/{token}/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');
