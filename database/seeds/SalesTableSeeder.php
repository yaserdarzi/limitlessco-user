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
            'title' => 'سایت های تخفیف گروهی',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_PERCENT_SITE,
        ]);
        //Sepehr
        \App\Sales::create([
            'title' => 'سپهر',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SEPEHR,
        ]);
        //Arabic Passenger
        \App\Sales::create([
            'title' => ' مسافران عرب زبان',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_ARABIC_PASSENGER,
        ]);
        //English Passenger
        \App\Sales::create([
            'title' => ' مسافران انگلیسی زبان',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_ENGLISH_PASSENGER,
        ]);
        //social
        \App\Sales::create([
            'title' => ' شبکه های اجتماعی',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_SOCIAL,
        ]);
        //celebrity
        \App\Sales::create([
            'title' => ' سلیبرتی ها ',
            'logo' => "",
            'type' => \App\Inside\Constants::SALES_TYPE_CELEBRITY,
        ]);

    }
}
