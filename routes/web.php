<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog');
Route::get('/producto/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/carrito/productos', [ProductController::class, 'cartData'])
    ->middleware('throttle:60,1')
    ->name('cart.data');
