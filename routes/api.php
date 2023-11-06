<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AssetTypeController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user/current', [AuthController::class, 'getCurrentUser']);

    Route::get('/user', [AuthController::class, 'getUsers']);
    Route::put('/user/user-name', [AuthController::class, 'changeUname']);
    Route::put('/user/email', [AuthController::class, 'changeEmail']);
    Route::get('/users/by-department', [AuthController::class, 'getUsersByDep']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/user/create', [AuthController::class, 'addUser']);

    Route::post('/request/create', [RequestController::class, 'makeRequest']);
    Route::put('/request/approve', [RequestController::class, 'approveRequest']);
    Route::get('/requests', [RequestController::class, 'getRequests']);
    Route::get('/my-requests', [RequestController::class, 'getMyRequests']);

    Route::post('/asset/create', [AssetController::class, 'makeAsset']);
    Route::get('/asset-type', [AssetTypeController::class, 'getAssetTypes']);
    

    Route::get('/asset/myAssets', [AssetController::class, 'myAssets']);
    Route::get('/assets', [AssetController::class, 'getAllAssets']);
    Route::put('/asset/accept', [AssetController::class, 'acceptAsset']);

    Route::post('/purchase', [PurchaseController::class, 'makePurchaseFromRequest']);
    Route::get('/purchases', [PurchaseController::class, 'getPurchases']);
    Route::get('/purchases/received', [PurchaseController::class, 'getReceivedPurchases']);
    Route::post('/purchase/generate-pdf', [PurchaseController::class, 'generatePurchaseDocument']);
    Route::put('/purchase/cancel', [PurchaseController::class, 'cancelPurchase']);
    Route::put('/purchase/receive', [PurchaseController::class, 'receivePurchase']);

    Route::post('/ticket', [TicketController::class, 'createTicket']);
    Route::get('/tickets', [TicketController::class, 'getAllTickets']);
    Route::get('/tickets/my-tickets', [TicketController::class, 'getMyTickets']);
    
    Route::get('/locations', [LocationController::class, 'getLocations']);
    Route::get('/priorities', [PriorityController::class, 'getPriorities']);
});

Route::post('/login', [AuthController::class, 'login']);
