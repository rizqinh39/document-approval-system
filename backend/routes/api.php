<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermohonanController;
use App\Http\Controllers\Api\PenilaianController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('v1')->middleware(['ensure.qi.signature'])->group(function () {
    
    // Public routes (Signature required, no auth token required yet)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Dashboard
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/chart-monthly', [DashboardController::class, 'chartMonthly']);

        // Permohonan
        Route::get('/permohonan', [PermohonanController::class, 'index']);
        Route::post('/permohonan', [PermohonanController::class, 'store']);
        Route::get('/permohonan/{id}', [PermohonanController::class, 'show']);
        Route::put('/permohonan/{id}', [PermohonanController::class, 'update']);
        Route::post('/permohonan/{id}/submit', [PermohonanController::class, 'submit']);
        Route::post('/permohonan/{id}/upload', [PermohonanController::class, 'uploadDocument']);
        Route::post('/permohonan/{id}/documents', [PermohonanController::class, 'uploadDocument']);
        Route::delete('/permohonan/{id}/documents/{docId}', [PermohonanController::class, 'deleteDocument']);

        // Penilaian
        Route::get('/penilaian', [PenilaianController::class, 'index']);
        Route::get('/penilaian/queue', [PenilaianController::class, 'index']);
        Route::post('/penilaian/{id}/review', [PenilaianController::class, 'review']);
    });
});
