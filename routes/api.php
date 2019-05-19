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

Route::namespace('Api\V1\CP')->prefix('/v1/cp/')->group(function () {

    //Register
    Route::namespace('Register')->prefix('/register/')->group(function () {

        //Auth
        Route::post('otp/sms', 'OTPController@smsOTP');
        Route::post('otp/verify', 'OTPController@verifyOTP');

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

            Route::get('/a', function () {
                return 1;
            });

//            Route::middleware('cp.supplier.auth')->group(function () {
//                Route::post('store', 'OTPController@Register');
//
//            });
        });

    });


});