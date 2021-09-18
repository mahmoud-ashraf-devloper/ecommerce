<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\ProductController;

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

Route::get('categories/show/{category}',[CategoryController::class, 'showCategoryWithProducts'])->name('category.show');


// Products Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/show/{product}', [ProductController::class, 'show']);


