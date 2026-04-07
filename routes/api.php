<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\PaymentsController;
use Illuminate\Http\Request;
use App\Http\Controllers\ConfigurationRemiseController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'App\Api\v1\Controllers'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('users', ['uses' => 'UserController@index']);
    });
});

// routes/api.php
Route::get('configuration-remise', [ConfigurationRemiseController::class, 'index'])->name('configuration-remise.index');
Route::post('configuration-remise', [ConfigurationRemiseController::class, 'update'])->name('configuration-remise.update');

Route::post('login',[LoginController::class,'apiLogin'])->name('loginapi');

Route::get('dashboard',[\App\Api\v1\Controllers\DashboardApi::class,'index'])->name('dashboard.api');

Route::get('clients', [ClientsController::class, 'getAll'])->name('clients.api');


Route::group(['prefix'=>"payments"],function(){
    Route::get("/",[PaymentsController::class,'getPayments'])->name('api.payments.getAll');
    Route::post("/delete/{payment}",[PaymentsController::class,'destroyAPI'])->name('api.payments.destroy');
    Route::post("/{payment}",[PaymentsController::class,'update'])->name('api.payments.update');
});