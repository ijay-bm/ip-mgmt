<?php

use App\Http\Controllers\V1\IpAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::resource('/ip-addresses', IpAddressController::class);
});
