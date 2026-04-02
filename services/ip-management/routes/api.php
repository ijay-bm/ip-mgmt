<?php

use Illuminate\Support\Facades\Route;

Route::get('/public-test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/private-test', function () {
        return response()->json(auth()->user());
    });
});
