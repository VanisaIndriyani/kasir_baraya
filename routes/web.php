<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPrintSettingsController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminSalesController;
use App\Http\Controllers\KasirApiController;
use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/kasir/index.php'));

Route::get('/kasir', fn () => redirect('/kasir/index.php'));
Route::get('/kasir/index.php', [KasirController::class, 'index']);
Route::get('/kasir/receipt.php', [KasirController::class, 'receipt']);
Route::get('/kasir/receipt.txt', [KasirController::class, 'receiptText']);

Route::get('/kasir/api/products.php', [KasirApiController::class, 'products']);
Route::match(['GET', 'POST'], '/kasir/api/cart.php', [KasirApiController::class, 'cart']);
Route::post('/kasir/api/checkout.php', [KasirApiController::class, 'checkout']);
Route::post('/kasir/api/print_server.php', [KasirApiController::class, 'printServer']);

Route::get('/admin/login.php', [AdminAuthController::class, 'showLogin']);
Route::post('/admin/login.php', [AdminAuthController::class, 'login']);
Route::get('/admin/logout.php', [AdminAuthController::class, 'logout']);

Route::middleware('admin')->group(function () {
    Route::get('/admin/index.php', [AdminDashboardController::class, 'index']);

    Route::get('/admin/products.php', [AdminProductController::class, 'index']);
    Route::post('/admin/actions/product_save.php', [AdminProductController::class, 'save']);
    Route::post('/admin/actions/product_delete.php', [AdminProductController::class, 'delete']);

    Route::get('/admin/sales.php', [AdminSalesController::class, 'index']);
    Route::get('/admin/api/transaction_detail.php', [AdminSalesController::class, 'transactionDetail']);

    Route::get('/admin/print_settings.php', [AdminPrintSettingsController::class, 'index']);
    Route::post('/admin/actions/print_settings_save.php', [AdminPrintSettingsController::class, 'save']);
    Route::post('/admin/actions/print_test.php', [AdminPrintSettingsController::class, 'testPrint']);
});
