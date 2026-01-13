<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
