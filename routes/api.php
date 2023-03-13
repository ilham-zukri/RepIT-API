<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/current', [AuthController::class, 'getCurrentUser']);
    Route::get('/user', [UserController::class, 'getUsers']);
    Route::get('/asset/myAssets', [AssetController::class, 'myAssets']);
});

Route::post('/login', [AuthController::class, 'login']);
