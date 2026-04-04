<?php

use App\Http\Controllers\V1\AuditLogIndexController;
use App\Http\Controllers\V1\IpAddressController;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::resource('/ip-addresses', IpAddressController::class);

    Route::get('/audit-logs', AuditLogIndexController::class)
        ->name('audit-logs.index')
        ->middleware([EnsureSuperAdmin::class]);
});
