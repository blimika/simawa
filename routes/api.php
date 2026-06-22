<?php
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MessageController;

Route::post('/webhook/gowa', [WebhookController::class, 'handle']);
Route::post('/send', [MessageController::class, 'send']);
