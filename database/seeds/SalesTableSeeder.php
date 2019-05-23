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
        //Api
        \App\Sales::create([
            'title' => 'فروش کلان آنلاین',
            'logo' => "API.svg",
            'type' => \App\Inside\Constants::SALES_TYPE_API,
        ]);
        //Just Kish
        \App\Sales::create([
            'title' => 'جاست کیش',
            'logo' => "justkish.png",
            'type' => \App\Inside\Constants::SALES_TYPE_JUSTKISH,
        ]);
        //Agency
        \App\Sales::create([
            'title' => 'آژانس ها',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_AGENCY,
        ]);
        //Percent Site
        \App\Sales::create([
            'title' => 'سایت های تخفیف',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_PERCENT_SITE,
        ]);
        //Sepehr
        \App\Sales::create([
            'title' => 'سپهر',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SEPEHR,
        ]);

    }
}
