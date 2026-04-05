<?php

use App\Http\Controllers\V1\AuditLogIndexController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    Route::get('/audit-logs', AuditLogIndexController::class)
        ->name('audit-logs.index')
        ->middleware([EnsureSuperAdmin::class]);
});

Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
