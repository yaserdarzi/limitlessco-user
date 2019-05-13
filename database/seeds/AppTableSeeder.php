<?php

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
            'type_app_child' => 'justkish',
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


    }
}
