<?php

use App\Http\Controllers\Api\Admin\Auth\LoginController;
use App\Http\Controllers\Api\Admin\DashboardController;
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
});