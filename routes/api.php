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
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SparePartTypeController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\SparePartRequestController;
use App\Http\Controllers\SparePartPurchaseController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user/current', [AuthController::class, 'getCurrentUser']);

    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::get('/users/by-department', [AuthController::class, 'getUsersByDep']);
    Route::get('/users/by-location-department', [AuthController::class, 'getUsersByLocationAndDep']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/user/create', [AuthController::class, 'addUser']);
    Route::get('/user/roles', [AuthController::class, 'getRole']);
    Route::get('/roles', [AuthController::class, 'getRolesForList']);

    Route::put('/user', [AuthController::class, 'editUser']);
    Route::put('/user/active', [AuthController::class, 'setActive']);
    Route::put('/user/user-name', [AuthController::class, 'changeUname']);
    Route::put('/user/full-name', [AuthController::class, 'changeFullName']);
    Route::put('/user/email', [AuthController::class, 'changeEmail']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);
    Route::put('/user/password/reset', [AuthController::class, 'resetPassword']);
    Route::put('/user/role', [AuthController::class, 'changeRole']);
    Route::put('/user/branch', [AuthController::class, 'changeBranch']);
    Route::put('/user/department', [AuthController::class, 'changeDepartment']);



    Route::post('/request/create', [RequestController::class, 'makeRequest']);
    Route::put('/request/approve', [RequestController::class, 'approveRequest']);
    Route::get('/requests', [RequestController::class, 'getRequests']);
    Route::get('/my-requests', [RequestController::class, 'getMyRequests']);

    Route::get('spare-parts/types', [SparePartTypeController::class, 'getTypes']);
    Route::post('/spare-parts/request', [SparePartRequestController::class, 'makeSparepartRequest']);
    Route::get('/spare-parts/requests', [SparePartRequestController::class, 'getSparepartRequests']);
    Route::put('/spare-parts/request/approve', [SparePartRequestController::class, 'approveSparepartRequest']);

    Route::post('/spare-parts/purchase', [SparePartPurchaseController::class, 'makePurchase']);
    Route::get('/spare-parts/purchases', [SparePartPurchaseController::class, 'getPurchases']);
    Route::get('/spare-parts/purchases/received', [SparePartPurchaseController::class, 'getReceivedPurchases']);
    Route::put('/spare-parts/purchase/cancel', [SparePartPurchaseController::class, 'cancelPurchase']);
    Route::put('/spare-parts/purchase/receive', [SparePartPurchaseController::class, 'receivePurchase']);
    
    Route::post('/spare-parts', [SparePartController::class, 'makeSparePart']);
    Route::get('/spare-parts', [SparePartController::class, 'getAllSpareParts']);
    Route::put('/spare-parts/deploy', [SparePartController::class, 'deploySpareParts']);


    Route::post('/asset/create', [AssetController::class, 'makeAsset']);
    Route::get('/asset-type', [AssetTypeController::class, 'getAssetTypes']);
    Route::get('/asset-list', [AssetController::class, 'myAssetList']);
    

    Route::get('/asset/myAssets', [AssetController::class, 'myAssets']);
    Route::get('/assets', [AssetController::class, 'getAllAssets']);
    Route::put('/asset/accept', [AssetController::class, 'acceptAsset']);
    Route::get('/asset/ticket-history', [AssetController::class, 'getAssetTicketHistory']);
    Route::get('/asset/attached-spare-parts', [AssetController::class, 'getAssetAttachedSpareParts']);
    Route::get('/asset/qr-code', [AssetController::class, 'getAssetByQRCode']);
    Route::put('/asset/scrap', [AssetController::class, 'scrapAsset']);
    Route::put('/asset/transfer', [AssetController::class, 'transferAsset']);
    Route::put('/asset/reserve', [AssetController::class, 'reserveAsset']);

    Route::post('/purchase', [PurchaseController::class, 'makePurchaseFromRequest']);
    Route::get('/purchases', [PurchaseController::class, 'getPurchases']);
    Route::get('/purchases/received', [PurchaseController::class, 'getReceivedPurchases']);
    Route::post('/purchase/generate-pdf', [PurchaseController::class, 'generatePurchaseDocument']);
    Route::put('/purchase/cancel', [PurchaseController::class, 'cancelPurchase']);
    Route::put('/purchase/receive', [PurchaseController::class, 'receivePurchase']);
    Route::get('/purchase/assets', [PurchaseController::class, 'getPurchasedAssets']);

    Route::post('/ticket', [TicketController::class, 'createTicket']);
    Route::get('/tickets', [TicketController::class, 'getAllTickets']);
    Route::get('/tickets/my-tickets', [TicketController::class, 'getMyTickets']);
    Route::get('/tickets/handled-tickets', [TicketController::class, 'getHandledTickets']);
    
    Route::put('/ticket/handle', [TicketController::class, 'handleTicket']);
    Route::put('/ticket/progress', [TicketController::class, 'progressTicket']);
    Route::put('/ticket/hold', [TicketController::class, 'holdTicket']);
    Route::put('/ticket/ToBeReviewed', [TicketController::class, 'ToBeReviewedTicket']);
    Route::put('/ticket/close', [TicketController::class, 'closeTicket']);

    Route::get('/locations', [LocationController::class, 'getLocations']);
    Route::get('/priorities', [PriorityController::class, 'getPriorities']);
    Route::get('/departments', [DepartmentController::class, 'getDepartmentsForList']);
    Route::get('/ticket-categories', [TicketCategoryController::class, 'getCategories']);
});

Route::post('/login', [AuthController::class, 'login']);
