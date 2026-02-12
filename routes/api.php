<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminSellerController;
use App\Http\Controllers\ProductController;

Route::post('admin/login', [AdminAuthController::class, 'adminLogin']);
Route::post('seller/login', [AdminAuthController::class, 'sellerLogin']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('admin/sellers', [AdminSellerController::class, 'store']);
    Route::get('admin/sellers', [AdminSellerController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'seller'])->group(function () {
    Route::post('seller/products', [ProductController::class, 'store']);
    Route::get('seller/products', [ProductController::class, 'index']);
     Route::get('seller/products/{product}/pdf', [ProductController::class, 'pdf']);
    Route::delete('seller/products/{product}', [ProductController::class, 'destroy']);
});


 