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

Route::namespace('Api\V1')->prefix('/v1/')->group(function () {

    //Get Supplier Active For Sales
    Route::get('app/get/supplier/active/sales', 'AppController@appGetSupplierActiveSales');

});
Route::namespace('Api\V1\CP')->prefix('/v1/cp/')->group(function () {

    //Register
    Route::namespace('Register')->prefix('/register/')->group(function () {

        //Auth
        Route::post('otp/sms', 'OTPController@smsOTP');
        Route::post('otp/verify', 'OTPController@verifyOTP');

        //App
        Route::get('app/supplier', 'AppController@appSupplier');
        Route::get('app/agency', 'AppController@appAgency');

        //Store Register
        Route::middleware('cp.register.auth')->group(function () {
            Route::post('store', 'OTPController@Register');

        });
    });

    //Supplier
    Route::namespace('Supplier')->prefix('/supplier/')->group(function () {

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
        });
        Route::middleware(['cp.supplier.app.check', 'cp.supplier.auth'])->group(function () {

            //Supplier Init
            Route::get('init', 'SupplierController@index');

            //App Checker
            Route::get('app/checker', 'AppController@appChecker');

            //Sales
            Route::get('sales', 'SalesController@index');
            Route::post('sales', 'SalesController@store');
        });

    });

    //Agency
    Route::namespace('Agency')->prefix('/agency/')->group(function () {

        //Zarinpall Callback
        Route::any('shoppingPaymentPortalCallback', 'Payment\ZarinpallController@portalCallback')->name('api.cp.agency.shopping.portal.callback');

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
        });
        Route::middleware(['cp.agency.app.check', 'cp.agency.auth'])->group(function () {

            //Supplier Init
            Route::get('init', 'AgencyController@index');

            //App Checker
            Route::get('app/checker', 'AppController@appChecker');

            Route::middleware(['cp.agency.app.name'])->group(function () {

                //Shopping
                Route::delete('shoppingBag', 'ShoppingBagController@destroyAll');
                Route::resource('shoppingBag', 'ShoppingBagController');

            });

            //Payment
            Route::post('checkout', 'PaymentController@checkout');
            Route::post('payment', 'PaymentController@store');
        });

    });

});