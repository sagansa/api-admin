<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecruitmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/email/resend', [AuthController::class, 'resend'])->name('verification.send');

    Route::get('/profile', [RecruitmentController::class, 'getDetails']);
    Route::post('/profile', [RecruitmentController::class, 'updateDetails']);
    Route::post('/profile/submit', [RecruitmentController::class, 'submitProfile']);
    
    Route::post('/profile/experience', [RecruitmentController::class, 'addExperience']);
    Route::put('/profile/experience/{id}', [RecruitmentController::class, 'updateExperience']);
    Route::delete('/profile/experience/{id}', [RecruitmentController::class, 'deleteExperience']);
    
    Route::delete('/profile/image', [RecruitmentController::class, 'deleteImage']);
});
