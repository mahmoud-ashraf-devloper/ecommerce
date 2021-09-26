<?php

use App\Http\Controllers\Api\Admin\Auth\LoginController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\ColorController;
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
    Route::post('products/{productId}/update/images/{productImageId}', [ProductController::class, 'updateProductImages']);
    Route::post('products/{productId}/update-set-main/images/{imageId}', [ProductController::class, 'setImageAsMainImage']);
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


    // colors
    Route::get('colors', [ColorController::class, 'index'])->name('get-all-colors');
    Route::post('colors', [ColorController::class, 'store'])->name('add-new-color');
    Route::post('colors/{colorId}/delete', [ColorController::class, 'addToTrash'])->name('color-add-to-trash');
    Route::get('colors/trashed', [ColorController::class, 'getTrashedColors'])->name('get-trashed-colors');
    Route::post('colors/{colorId}/force-delete', [ColorController::class, 'forceDelete'])->name('force-delete-color');
    Route::post('colors/{colorId}/restore', [ColorController::class, 'restoreTrashedColor'])->name('restore-color');

    // colors-products
    Route::post('products/{productsId}/add-color/{colorId}', [ColorController::class, 'addColorsToProduct'])->name('add-color-to-product');
    Route::post('products/{productsId}/remove-color/{colorId}', [ColorController::class, 'removeColorFromProduct'])->name('add-color-to-product');
});