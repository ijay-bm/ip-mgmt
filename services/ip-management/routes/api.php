<?php

use App\Http\Controllers\V1\IpAddress\AuditLogIndexController;
use App\Http\Controllers\V1\IpAddressController;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/ip-addresses/audit-logs', AuditLogIndexController::class)
        ->name('ip-addresses.audit-logs.index')
        ->middleware([EnsureSuperAdmin::class]);

    Route::resource('/ip-addresses', IpAddressController::class);
});
