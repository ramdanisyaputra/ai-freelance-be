<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProposalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('freelancerProfile');
    });
    Route::post('/profile/update', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::put('/password/update', [App\Http\Controllers\Api\ProfileController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Proposal routes
    Route::post('/generate-proposal', [ProposalController::class, 'generate']);
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::get('/proposals/{id}', [ProposalController::class, 'show']);
    Route::put('/proposals/{id}', [ProposalController::class, 'update']);
    Route::get('/proposals/{id}/pdf', [ProposalController::class, 'exportPdf']);
    Route::delete('/proposals/{id}', [ProposalController::class, 'destroy']);
    
    // Image upload
    Route::post('/upload-image', [App\Http\Controllers\Api\ImageUploadController::class, 'upload']);
});

// AI Service Callback (token auth required)
Route::post('/ai/callback/proposal/{id}', [ProposalController::class, 'callback'])
    ->middleware('ai.token')
    ->name('ai.callback.proposal');
