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

Route::namespace('Api\V1\Auth')->prefix('/v1/')->group(function () {

    //Auth
    Route::post('auth/otp/sms', 'OTPController@smsOTP');
    Route::post('auth/otp/verify', 'OTPController@verifyOTP');
});
Route::namespace('Api\V1')->prefix('/v1/')->group(function () {

    //Get Apps
    Route::get('app', 'AppController@getApp');

});
