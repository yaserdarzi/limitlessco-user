<?php

use Illuminate\Database\Seeder;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $appEntertainment = 2;
        $appHotel = 2;
        //Api
        \App\Sales::create([
            'app_id' => $appEntertainment,
            'title' => 'فروش کلان آنلاین',
            'logo' => "API.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_API,
        ]);
        \App\Sales::create([
            'app_id' => $appHotel,
            'title' => 'فروش کلان آنلاین',
            'logo' => "API.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_API,
        ]);
        //Just Kish
        \App\Sales::create([
            'app_id' => $appEntertainment,
            'title' => 'جاست کیش',
            'logo' => "justkish.png",
            'type' => \App\Inside\Constants::SALES_TYPE_JUSTKISH,
        ]);
        \App\Sales::create([
            'app_id' => $appHotel,
            'title' => 'جاست کیش',
            'logo' => "justkish.png",
            'type' => \App\Inside\Constants::SALES_TYPE_JUSTKISH,
        ]);
        //Agency
        \App\Sales::create([
            'app_id' => $appEntertainment,
            'title' => 'آژانس ها',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_AGENCY,
        ]);
        \App\Sales::create([
            'app_id' => $appHotel,
            'title' => 'آژانس ها',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_AGENCY,
        ]);
        //Percent Site
        \App\Sales::create([
            'app_id' => $appEntertainment,
            'title' => 'سایت های تخفیف',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_PERCENT_SITE,
        ]);
        \App\Sales::create([
            'app_id' => $appHotel,
            'title' => 'سایت های تخفیف',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_PERCENT_SITE,
        ]);
        //Sepehr
        \App\Sales::create([
            'app_id' => $appEntertainment,
            'title' => 'سپهر',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SEPEHR,
        ]);
        \App\Sales::create([
            'app_id' => $appHotel,
            'title' => 'سپهر',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SEPEHR,
        ]);

    }
}
