<?php

use App\Http\Controllers\Api\RecruitmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/recruitment/profile', [RecruitmentController::class, 'getDetails']);
    Route::post('/recruitment/profile', [RecruitmentController::class, 'updateDetails']);
    Route::post('/recruitment/profile/submit', [RecruitmentController::class, 'submitProfile']);
    
    Route::post('/recruitment/experience', [RecruitmentController::class, 'addExperience']);
    Route::put('/recruitment/experience/{id}', [RecruitmentController::class, 'updateExperience']);
    Route::delete('/recruitment/experience/{id}', [RecruitmentController::class, 'deleteExperience']);
    
    Route::post('/recruitment/profile/image/delete', [RecruitmentController::class, 'deleteImage']);
});
