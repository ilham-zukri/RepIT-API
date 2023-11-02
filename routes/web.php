<?php

use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/purchase-document', function () {
    $pdf = Pdf::loadView('purchase_document')->setPaper('a5', 'landscape');
    return $pdf->download('masa_iya.pdf');
});

Route::get('/pdf', [PurchaseController::class, 'testPdf']);
