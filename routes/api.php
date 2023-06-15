<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user/current', [AuthController::class, 'getCurrentUser']);

    Route::get('/user', [AuthController::class, 'getUsers']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/user/create', [AuthController::class, 'addUser']);

    Route::post('/request/create', [RequestController::class, 'makeRequest']);
    Route::put('/request/approve', [RequestController::class, 'approveRequest']);

    Route::post('/asset/create', [AssetController::class, 'makeAsset']);
    
    Route::post('/purchase/create', [PurchaseController::class, 'makePurchaseFromRequest']);

    Route::get('/asset/myAssets', [AssetController::class, 'myAssets']);
    Route::get('/purchase', [PurchaseController::class, 'getPurchases']);

    
});

Route::post('/login', [AuthController::class, 'login']);
