<?php

use App\Http\Controllers\Api\Admin\Auth\LoginController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\ProductController;
use App\Http\Controllers\Api\App\SizeController;
use Illuminate\Support\Facades\Route;

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

    // Products
    Route::post('products/store', [ProductController::class, 'store'])->name('add-new-product');
    Route::post('products/update/images/{productImage}', [ProductController::class, 'updateProductImages']);
    Route::post('products/update-set-main/images/{imageId}', [ProductController::class, 'setImageAsMainImage']);
    Route::post('products/update/{productId}', [ProductController::class, 'updateProductData']);
    Route::post('products/destroy/{productId}', [ProductController::class, 'destroy']);
    Route::get('products/trashed', [ProductController::class, 'getTrashedProducts']);
    Route::post('products/force-delete/{productId}', [ProductController::class, 'forceDelete']);

    // categories
    Route::get('categories',[CategoryController::class, 'index'])->name('categories');
    Route::post('categories/store',[CategoryController::class, 'store'])->name('category.store');
    Route::post('categories/update/{categoryId}',[CategoryController::class, 'edit'])->name('category.update');
    Route::post('categories/destroy/{categoryId}',[CategoryController::class, 'destroy'])->name('category.delete');
    Route::post('categories/force-delete/{categoryId}',[CategoryController::class, 'forceDelete'])->name('category.forceDelete');

    // sizes
    Route::post('products/add-size-to-product/{productId}', [SizeController::class, 'addSizeToProduct'])->name('add-size-to-product');
    Route::post('products/{productId}/delete-size/{sizeId}', [SizeController::class, 'deleteSizeFromProduct'])->name('delete-size');
    Route::get('products/sizes', [SizeController::class, 'getAllSizes'])->name('getAllSizes');
    Route::post('products/add-size', [SizeController::class, 'addNewSize'])->name('add-new-size');
    Route::post('products/sizes/edit/{sizeId}', [SizeController::class, 'editSize'])->name('edit-size');

});