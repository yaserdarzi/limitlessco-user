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

    //Generate Ticket
    Route::post('/save/ticket/{shopping_id}', 'TicketController@store');

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
        Route::get('app/api', 'AppController@appApi');

        //Store Register
        Route::middleware('cp.register.auth')->group(function () {
            Route::post('store', 'OTPController@Register');

        });
    });

    //Supplier
    Route::namespace('Supplier')->prefix('/supplier/')->group(function () {

        //Zarinpall Callback
        Route::any('shoppingPaymentPortalCallback', 'Payment\ZarinpallController@portalCallback')->name('api.cp.supplier.shopping.portal.callback');

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
            Route::post('login', 'OTPController@login');
        });

        Route::middleware(['cp.supplier.app.check', 'cp.supplier.auth'])->group(function () {

            //Supplier Init
            Route::get('init', 'SupplierController@index');
            Route::post('update', 'SupplierController@update');
            Route::post('user/update', 'SupplierController@userUpdate');
            Route::post('user/change/password', 'SupplierController@userChangePassword');

            //App Checker
            Route::get('app/checker', 'AppController@appChecker');

            //Sales
            Route::get('sales', 'SalesController@index');
            Route::post('sales', 'SalesController@store');
            Route::post('sales/powerUp', 'SalesController@powerUp');

            //Voucher
            Route::get('voucher', 'VoucherController@index');
            Route::post('voucher', 'VoucherController@store');

            //Supplier Agency Category
            Route::post('agency/category/update/{supplier_agency_category_id}', 'SupplierAgencyCategoryController@update');
            Route::resource('agency/category', 'SupplierAgencyCategoryController');

            //Supplier Agency Request
            Route::resource('agency/request', 'SupplierAgencyRequestController');

            //Supplier Agency
            Route::post('agency/update/{supplier_agency_id}', 'SupplierAgencyController@update');
            Route::resource('agency', 'SupplierAgencyController');

            //Report
            Route::get('report/sales', 'ReportController@sales');
            Route::get('report/income', 'ReportController@income');
            Route::get('report/cancel', 'ReportController@cancel');
            Route::get('report/manifest', 'ReportController@manifest');

            //Setting
            Route::get('setting', 'SettingController@index');

            Route::middleware(['cp.supplier.app.name'])->group(function () {

                //Shopping
                Route::post('shoppingBag', 'ShoppingBagController@store');

            });

            //Payment Api
            Route::post('payment', 'PaymentController@store');

            //Service
            Route::post('service', 'ServiceController@store');

            //Supplier User
            Route::post('user/update/{supplier_user_id}', 'SupplierUserController@update');
            Route::resource('user', 'SupplierUserController');

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
            Route::post('login', 'OTPController@login');
        });

        Route::middleware(['cp.agency.app.check', 'cp.agency.auth'])->group(function () {

            //Get Supplier Active For Sales
            Route::get('app/get/supplier', 'AppController@getSupplier');

            //Agency Init
            Route::get('init', 'AgencyController@index');
            Route::post('update', 'AgencyController@update');
            Route::post('user/update', 'AgencyController@userUpdate');
            Route::post('user/change/password', 'AgencyController@userChangePassword');

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
            Route::post('ticket/send/mail', 'TicketController@ticketSendMail');

            //Agency User
            Route::post('user/update/{user_id}', 'AgencyUserController@update');
            Route::resource('user', 'AgencyUserController');

            //Wallet
            Route::get('wallet', 'WalletController@index');
            Route::post('wallet', 'WalletController@store');

            //Agency Agency Category
            Route::post('agency/category/update/{agency_agency_category_id}', 'AgencyAgencyCategoryController@update');
            Route::resource('agency/category', 'AgencyAgencyCategoryController');

            //Agency Agency Request
            Route::resource('agency/request', 'AgencyAgencyRequestController');

            //Agency Agency
            Route::post('agency/update/{agency_agency_id}', 'AgencyAgencyController@update');
            Route::resource('agency', 'AgencyAgencyController');

        });

    });

    //Api
    Route::namespace('Api')->prefix('/api/')->group(function () {

        //Zarinpall Callback
        Route::any('walletCallback', 'Payment\ZarinpallController@walletCallback')->name('api.cp.api.wallet.callback');

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
            Route::post('login', 'OTPController@login');
        });

        Route::middleware(['cp.api.app.check', 'cp.api.auth'])->group(function () {

            //Get Supplier Active For Sales
            Route::get('app/get/supplier', 'AppController@getSupplier');

            //Api Init
            Route::get('init', 'WebServiceController@index');
            Route::post('update', 'WebServiceController@update');
            Route::post('user/update', 'WebServiceController@userUpdate');
            Route::post('user/change/password', 'WebServiceController@userChangePassword');

            //App Checker
            Route::get('app/checker', 'AppController@appChecker');

            //Report
            Route::get('report/sales', 'ReportController@sales');
            Route::get('report/chart', 'ReportController@chart');

            //Ticket
            Route::get('getTicket', 'TicketController@show');
            Route::get('ticket', 'TicketController@index');
            Route::post('ticket/paymentToken', 'TicketController@ticketPaymentToken');

            //Api User
            Route::post('user/update/{user_id}', 'ApiUserController@update');
            Route::resource('user', 'ApiUserController');

            //Wallet
            Route::get('wallet', 'WalletController@index');
            Route::post('wallet', 'WalletController@store');

            //Hotel Payment
            Route::post('payment/hotel', 'PaymentController@PaymentHotel');

            //Setting
            Route::get('setting', 'SettingController@index');

        });

    });

    //Crm
    Route::namespace('Crm')->prefix('/crm/')->group(function () {

        //Auth
        Route::namespace('Auth')->prefix('/auth/')->group(function () {
            Route::post('otp/sms', 'OTPController@smsOTP');
            Route::post('otp/verify', 'OTPController@verifyOTP');
            Route::post('login', 'OTPController@login');
        });

//        Route::middleware(['cp.crm.auth'])->group(function () {

        //Api Init
        Route::get('init', 'CrmController@index');
        Route::post('user/update', 'CrmController@userUpdate');
        Route::post('user/change/password', 'CrmController@userChangePassword');

        //Register Agency with Api
        Route::post('register', 'RegisterController@store');

        //Checkout Supplier Income
        Route::post('checkout', 'CheckoutController@store');


//        });

    });


});