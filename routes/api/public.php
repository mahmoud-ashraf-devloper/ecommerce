<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\ColorController;
use App\Http\Controllers\Api\App\ProductController;
use App\Http\Controllers\Api\App\SizeController;

/*
|--------------------------------------------------------------------------
| API Routes Non Authenticated Users
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Categories Routes
Route::get('categories/show/{category}',[CategoryController::class, 'showCategoryWithProducts'])->name('category.show-with-products');
Route::get('categories',[CategoryController::class, 'index'])->name('category.index');


// Products Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/show/{product}', [ProductController::class, 'show']);

// sizes
Route::get('products/sizes/{productId}', [SizeController::class, 'avilableSizesForProduct'])->name('getAvilableSizesForProduct');

// colors
Route::get('products/{productId}/colors', [ColorController::class, 'getAllAvilableColorsForProduct'])->name('all-avilable-colors');