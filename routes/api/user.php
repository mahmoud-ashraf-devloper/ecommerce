<?php

use App\Http\Controllers\Api\App\CartController;
use App\Http\Controllers\Api\User\Auth\LoginController;
use App\Http\Controllers\Api\User\Auth\RegisterController;
use Illuminate\Http\Request;
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


Route::post('user/login', [LoginController::class, 'userLogin'])->name('user-login');
Route::post('user/register', [RegisterController::class, 'userRegister'])->name('user-register');

Route::group([
    'prefix' => 'users',
    'middleware' => ['auth:user-api', 'scopes:user'],
    'as' => 'user',
], function(){
    Route::get('cart', [CartController::class, 'index'])->name('get-shpping-cart-items');
    Route::post('cart/add/{productId}', [CartController::class, 'addToCart'])->name('add-to-cart');
    Route::post('cart/delete/{productId}', [CartController::class, 'removeFromTheCart'])->name('remove-to-cart');
});