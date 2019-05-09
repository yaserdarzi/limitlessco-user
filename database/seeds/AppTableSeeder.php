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
            'app' => 'G-market',
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
            'type_payment' => 'zarinpall',
            'info_payment' => (object)$info_payment,
            'type_sms' => 'kavenegar',
            'info_sms' => (object)$info_sms,
            'info' => ''
        ]);

    }
}
