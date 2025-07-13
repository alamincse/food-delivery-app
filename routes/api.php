<?php

use App\Http\Controllers\Api\DeliveryZoneController;
use App\Http\Controllers\Api\DeliveryAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('admin')->group(function () {
	Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin.guard'])->group(function () {
    	Route::post('/delivery-zones', [DeliveryZoneController::class, 'store']);
    	Route::post('/orders', [OrderController::class, 'order']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});


Route::prefix('delivery')->group(function () {
    Route::post('/login', [DeliveryAuthController::class, 'login']); 

    Route::middleware(['auth:sanctum', 'delivery.guard'])->group(function () {
    	Route::post('/orders/status', [OrderController::class, 'statusUpdate']);
    	
        Route::post('/logout', [DeliveryAuthController::class, 'logout']);
    });
});