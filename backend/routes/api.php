<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('email/send-token', [EmailVerificationController::class, 'sendToken']);
    Route::post('email/verify', [EmailVerificationController::class, 'verify']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('news', NewsController::class);
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings/password', [SettingController::class, 'updatePassword']);
    Route::put('settings/post-account', [SettingController::class, 'updatePostAccount'])->middleware('admin');
    Route::put('settings/research-prompt', [SettingController::class, 'updateResearchPrompt'])->middleware('admin');
});
