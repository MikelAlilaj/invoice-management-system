<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DiscountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::resource('products', ProductController::class)->only(['store', 'show', 'update', 'destroy', 'index']);
Route::resource('invoices', InvoiceController::class)->only(['store', 'show', 'update', 'destroy', 'index']);
Route::post('invoices/{invoice}/line-items', [InvoiceController::class, 'storeLineItems']);
Route::delete('invoices/{invoice}/line-items', [InvoiceController::class, 'destroyLineItems']);
Route::resource('discounts', DiscountController::class)->only(['store']);

