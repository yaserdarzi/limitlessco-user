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

            //Voucher
            Route::get('voucher', 'VoucherController@index');
            Route::post('voucher', 'VoucherController@store');
        });

    });

    //Agency
    Route::namespace('Agency')->prefix('/agency/')->group(function () {

        //Zarinpall Callback
        Route::any('shoppingPaymentPortalCallback', 'Payment\ZarinpallController@portalCallback')->name('api.cp.agency.shopping.portal.callback');
        Route::any('shoppingPaymentWalletPortalCallback', 'Payment\ZarinpallController@walletPortalCallback')->name('api.cp.agency.shopping.wallet.portal.callback');
        Route::any('walletCallback', 'Payment\ZarinpallController@walletCallback')->name('api.cp.agency.wallet.callback');

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
        });
        Route::middleware(['cp.agency.app.check', 'cp.agency.auth'])->group(function () {

            //Get Supplier Active For Sales
            Route::get('app/get/supplier', 'AppController@getSupplier');

            //Agency Init
            Route::get('init', 'AgencyController@index');
            Route::post('update', 'AgencyController@update');
            Route::post('user/update', 'AgencyController@userUpdate');

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

            //Report
            Route::get('report/sales', 'ReportController@sales');
            Route::get('report/chart', 'ReportController@chart');

            //Ticket
            Route::get('getTicket', 'TicketController@show');
            Route::get('ticket', 'TicketController@index');

            //Agency User
            Route::post('user/update/{user_id}', 'AgencyUserController@update');
            Route::resource('user', 'AgencyUserController');

            //Wallet
            Route::get('wallet', 'WalletController@index');
            Route::post('wallet', 'WalletController@store');

        });

    });

});