<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);

Route::post('/passwordForgot', [AuthController::class, 'forgotPassword']);

Route::post('/verify-code', [AuthController::class, 'verifyCode']);

Route::post('/resend-code', [AuthController::class, 'resendCode']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);

Route::post('/reset-password', [AuthController::class, 'resetPassword']);
