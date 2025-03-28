<?php

use Illuminate\Http\Request;

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
    Route::post('/api-login', 'ApiLoginController@login');
    Route::post('/api-import', 'ApiLoginController@importCsvDuplicate');

    Route::get('/dashboard-data', 'ApiDashboardController@dashboardData');

    
    Route::group(['prefix' => 'details'], function () {
        Route::get('/clients', 'ApiDetailsController@clientsDetails');
        Route::get('/offers', 'ApiDetailsController@offersDetails');
        Route::get('/payments', 'ApiDetailsController@paymentsDetails');
        Route::get('/invoice-lines', 'ApiDetailsController@invoiceLinesDetails');
    });

    Route::group(['prefix' => 'payments'], function () {
        Route::get('/{id}/soft-delete', 'ApiPaymentController@deletePayment');
        Route::get('/{id}/{amount}/soft-update', 'ApiPaymentController@updatePayment');
    });

    Route::group(['prefix' => 'reduction'], function () {
        Route::get('/value', 'ApiReductionController@reductionValue');
        Route::get('/{amount}/soft-update', 'ApiReductionController@updateReduction');
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('users', ['uses' => 'UserController@index']);
    });
});
