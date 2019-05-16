<?php

use Firebase\JWT\JWT;
use Illuminate\Database\Seeder;

class AppTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /////////////////////////////justkish/////////////////////
        $appJustkish = \App\App::create([
            'app' => 'just',
            'type_app' => 'justkish',
            'type_app_child' => '',
            'cash_back' => 0,
            'info' => ''
        ]);
        $apiJustkish = \App\Api::create([
            'name' => "justkish",
            'type' => "price",
            'price' => 0,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $apiJustkish->id,
            'app_id' => $appJustkish->id,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $appJustkish->type_app,
            'otp_template' => $appJustkish->type_app
        ];
        \App\ApiSetting::create([
            'api_id' => $appJustkish->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $object = array(
            "api_id" => $apiJustkish->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "justkish= " . $appSecret . "\n";
        /////////////////////////////justkish/////////////////////
        /////////////////////////////hotel/////////////////////
        $appHotel = \App\App::create([
            'app' => 'hotel',
            'type_app' => 'hotel',
            'type_app_child' => '',
            'cash_back' => 0,
            'info' => ''
        ]);
        $apiHotel = \App\Api::create([
            'name' => "hotel",
            'type' => "price",
            'price' => 0,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $apiHotel->id,
            'app_id' => $appHotel->id,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $appHotel->type_app,
            'otp_template' => $appHotel->type_app
        ];
        \App\ApiSetting::create([
            'api_id' => $apiHotel->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $object = array(
            'api_id' => $apiHotel->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "hotel= " . $appSecret . "\n";
        /////////////////////////////hotel/////////////////////
        /////////////////////////////hotelsunrise/////////////////////
        $appSunrise = \App\App::create([
            'app' => 'hotel',
            'type_app' => 'hotel',
            'type_app_child' => 'sunrise',
            'cash_back' => 0,
            'info' => ''
        ]);
        $apiSunrise = \App\Api::create([
            'name' => "sunrise",
            'type' => "percent",
            'percent' => 10,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $apiSunrise->id,
            'app_id' => $appSunrise->id,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $appSunrise->type_app,
            'otp_template' => $appSunrise->type_app
        ];
        \App\ApiSetting::create([
            'api_id' => $apiSunrise->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $object = array(
            "api_id" => $apiSunrise->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "hotel-sunrise= " . $appSecret . "\n";
        /////////////////////////////hotelsunrise/////////////////////

        \App\ApiApp::create([
            'api_id' => $apiHotel->id,
            'app_id' => $appSunrise->id,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);


    }
}
