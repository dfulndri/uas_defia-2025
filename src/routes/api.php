<?php

use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductApiController;

Route::middleware([ApiKeyAuth::class])->group(function () {
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::post('/products', [ProductApiController::class, 'store']);
    Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);
    Route::get('/products/{product}', [ProductApiController::class, 'show']);
});
