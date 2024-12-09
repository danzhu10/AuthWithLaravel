<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\VerifyEmailController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//api yang membutuhkan token untuk mengaksesnya
Route::middleware(['auth:sanctum'])->group(function (){
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
});

//user auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//untuk verifikasi email user setelah register
Route::post('email/verification', [VerifyEmailController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');

//email verification untuk mengirimkan notifikasi kepada email user
Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');

//reset password
Route::post('forgot-password', [ResetPasswordController::class, 'forgotPassword']);
Route::post('change-password', [ResetPasswordController::class, 'changePassword']);

