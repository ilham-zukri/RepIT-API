<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\RequestContext;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/current', [AuthController::class, 'getCurrentUser']);

    Route::get('/user', [AuthController::class, 'getUsers']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/user/create', [AuthController::class, 'addUser']);

    Route::post('/request/create', [RequestController::class, 'makeRequest']);

    Route::get('/asset/myAssets', [AssetController::class, 'myAssets']);
    Route::get('/purchase', [PurchaseController::class, 'getPurchases']);

    
});

Route::post('/login', [AuthController::class, 'login']);
