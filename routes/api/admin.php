<?php

use App\Http\Controllers\Api\Admin\Auth\LoginController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| API Routes For User
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('admin/login', [LoginController::class, 'adminLogin'])->name('admin-login');

Route::group([
    'prefix'=>'admin',
    'middleware' => ['auth:admin-api', 'scopes:admin'],
    'as' => 'admin.',
], function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('categories',[CategoryController::class, 'index'])->name('categories');
    Route::post('categories/store',[CategoryController::class, 'store'])->name('category.store');



    // Products
    Route::post('products/store', [ProductController::class, 'store'])->name('add-new-product');
    Route::post('products/update/images/{productImage}', [ProductController::class, 'updateProductImages']);
    Route::post('products/update-set-main/images/{imageId}', [ProductController::class, 'setImageAsMainImage']);
    Route::post('products/update/{productId}', [ProductController::class, 'updateProductData']);
    Route::post('products/destroy/{productId}', [ProductController::class, 'destroy']);
    Route::get('products/trashed', [ProductController::class, 'getTrashedProducts']);
    Route::post('products/force-delete/{productId}', [ProductController::class, 'forceDelete']);

});