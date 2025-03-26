<?php

use App\Api\v1\Controllers\DashBoardApi\DetailsTotalController;
use App\Api\v1\Controllers\DashBoardApi\RemiseController;
use App\Api\V1\Controllers\DashBoardApi\TestController;
use App\Api\v1\Controllers\UserController;
use App\Api\v1\Controllers\DashBoardApi\DashBoardController;
use Illuminate\Http\Request;
use App\Api\v1\Controllers\ConnexionController;


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

    Route::prefix('/spring')->group(function (){
            
            Route::post('/login',[ConnexionController::class,'login']);
            Route::get('/dashboard',[DashBoardController::class,'getStats'])->name("dashboard.stats");
            Route::get("/top-clients",[DashBoardController::class,'getBestClients']);
            Route::get("/offer-amount",[DashBoardController::class,'getOfferWon']);
            Route::get("/earnMonth",[DashBoardController::class,'getVentesMensuelles']);
            Route::get("/best-products",[DashBoardController::class,'getBestProducts']);
            Route::get("/invoice-total",[DashBoardController::class,"getInvoicesTotal"]);
            Route::get("/invoices",[DetailsTotalController::class,"getInvoices"]);
            Route::post("/invoice",[DetailsTotalController::class,"getInvoiceById"]);
            Route::post("/payment_invoice",[DetailsTotalController::class,'getPayments']);
            Route::post("/edit-payment",[DetailsTotalController::class,"editPayment"]);
            Route::post("/delete-payment",[DetailsTotalController::class,"deletePayment"]);
            Route::post("/payment",[DetailsTotalController::class,'getPaymentById']);
            Route::post("/remise",[RemiseController::class,'addRemise']);
            Route::get("/remises",[RemiseController::class,"getRemise"]);
    });
    


