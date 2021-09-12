<?php

use App\Http\Controllers\Api\User\Auth\LoginController;
use App\Http\Controllers\Api\User\Auth\RegisterController;
use App\Http\Controllers\Api\User\DashboardController;
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
Route::post('/user/register', [RegisterController::class, 'userRegister'])->name('user-register');

Route::group([
    'prefix' => 'user',
    'middleware' => ['auth:user-api', 'scopes:user'],
    'as' => 'user',
], function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});