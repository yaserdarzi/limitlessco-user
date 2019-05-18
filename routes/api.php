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

Route::namespace('Api\V1\CP\Register')->prefix('/v1/cp/register/')->group(function () {

    //Auth
    Route::post('otp/sms', 'OTPController@smsOTP');
    Route::post('otp/verify', 'OTPController@verifyOTP');

    Route::middleware('cp.register.auth')->group(function () {
        Route::post('store', 'OTPController@Register');

    });
});
Route::namespace('Api\V1')->prefix('/v1/')->group(function () {

    //Get Apps
    Route::get('apiChecker', 'AppController@apiChecker');

});
