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
        $app = \App\App::create([
            'app' => 'just',
            'type_app' => 'justkish',
            'type_app_child' => '',
            'cash_back' => 0,
            'info' => ''
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $app->type_app,
            'otp_template' => $app->type_app
        ];
        \App\AppSetting::create([
            'app_id' => $app->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $app = \App\App::create([
            'app' => 'hotel',
            'type_app' => 'hotel',
            'type_app_child' => '',
            'cash_back' => 0,
            'info' => ''
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $app->type_app,
            'otp_template' => $app->type_app
        ];
        \App\AppSetting::create([
            'app_id' => $app->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $app = \App\App::create([
            'app' => 'hotel',
            'type_app' => 'hotel',
            'type_app_child' => 'sunrise',
            'cash_back' => 0,
            'info' => ''
        ]);
        $info_payment = ['marchedcode' => 'q223qwecasddas'];
        $info_sms = [
            'kavenegar_api_key' => $app->type_app,
            'otp_template' => $app->type_app
        ];
        \App\AppSetting::create([
            'app_id' => $app->id,
            'type_payment' => \App\Inside\Constants::MARKET_ZARINPAL,
            'info_payment' => (object)$info_payment,
            'type_sms' => \App\Inside\Constants::SMS_KAVENEGAR,
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);
        $api = \App\Api::create([
            'name' => "justkish",
            'type' => "price",
            'price' => 0,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $api->id,
            'app_id' => 1,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $object = array(
            "api_id" => $api->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "justkish= " . $appSecret . "\n";
        $api = \App\Api::create([
            'name' => "hotel",
            'type' => "price",
            'price' => 0,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $api->id,
            'app_id' => 2,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        \App\ApiApp::create([
            'api_id' => $api->id,
            'app_id' => 3,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $object = array(
            'api_id' => $api->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "hotel= " . $appSecret . "\n";
        $api = \App\Api::create([
            'name' => "sunrise",
            'type' => "percent",
            'percent' => 10,
            'income' => 0,
            'award' => 0,
            'info' => ""
        ]);
        \App\ApiApp::create([
            'api_id' => $api->id,
            'app_id' => 3,
            'info' => "",
            'created_at' => date('Y-m-d')
        ]);
        $object = array(
            "app_id" => $app->id,
        );
        $appSecret = JWT::encode($object, config("jwt.secret"));
        echo "hotel-sunrise= " . $appSecret . "\n";

    }
}
