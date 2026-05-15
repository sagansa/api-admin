<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecruitmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/email/resend', [AuthController::class, 'resend'])->name('verification.send');

    Route::get('/recruitment/profile', [RecruitmentController::class, 'getDetails']);
    Route::post('/recruitment/profile', [RecruitmentController::class, 'updateDetails']);
    Route::post('/recruitment/profile/submit', [RecruitmentController::class, 'submitProfile']);
    
    Route::post('/recruitment/experience', [RecruitmentController::class, 'addExperience']);
    Route::put('/recruitment/experience/{id}', [RecruitmentController::class, 'updateExperience']);
    Route::delete('/recruitment/experience/{id}', [RecruitmentController::class, 'deleteExperience']);
    
    Route::post('/recruitment/profile/image/delete', [RecruitmentController::class, 'deleteImage']);
});
