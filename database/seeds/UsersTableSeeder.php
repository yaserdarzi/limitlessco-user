<?php

use App\Agency;
use App\AgencyApp;
use App\AgencyUser;
use App\AgencyWallet;
use App\Api;
use App\ApiApp;
use App\ApiUser;
use App\ApiWallet;
use App\Inside\Constants;
use App\Supplier;
use App\SupplierApp;
use App\SupplierUser;
use App\SupplierWallet;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hashIds = new Hashids(config("config.hashIds"));
        //admin
        $refLink = $hashIds->encode(989111160804, intval(microtime(true)));
        $user = User::create([
            'phone' => "989111160804",
            'email' => 'yaser.darzi@gamil.com',
            'gmail' => 'yaser.darzi@gamil.com',
            'username' => 'yaser.darzi',
            'password_username' => 'yaserdarzi',
            'name' => "yaser darzi",
            "ref_link" => $refLink,
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'price' => 0,
        ]);
        \App\Crm::create([
            'user_id' => $user->id,
            'role' => Constants::ROLE_ADMIN,
            'status' => Constants::STATUS_ACTIVE,
        ]);
        //sun rise
        $refLink = $hashIds->encode(989347690939, intval(microtime(true)));
        $userSunrise = User::create([
            'phone' => "989347690939",
            'name' => "رحمانیان",
            "ref_link" => $refLink,
        ]);
        Wallet::create([
            'user_id' => $userSunrise->id,
            'price' => 0,
        ]);
        $supplier = Supplier::create([
            'name' => 'هتل سان رایز',
            'image' => '',
            'tell' => '',
            'status' => Constants::STATUS_ACTIVE,
            'type' => 'percent',
            'percent' => Constants::SUPPLIER_PERCENT_DEFAULT
        ]);
        SupplierUser::create([
            'user_id' => $userSunrise->id,
            'supplier_id' => $supplier->id,
            'type' => 'percent',
            'percent' => 100,
            'role' => Constants::ROLE_ADMIN
        ]);
        SupplierWallet::create([
            'supplier_id' => $supplier->id,
            'price' => 0
        ]);
        SupplierApp::create([
            'supplier_id' => $supplier->id,
            'app_id' => 2,
        ]);
        $data = [
            [
                'nameApi' => "سفر می",
                'phone' => 989354558796,
                'name' => "استیری",
                'username' => "safarme",
            ], [
                'nameApi' => "شرکت آرنیکا کیش (هتل یار)",
                'phone' => 989018718195,
                'name' => "شرکت آرنیکا کیش (هتل یار)",
                'username' => "hotelyar",
            ], [
                'nameApi' => "جا اینجاست",
                'phone' => 989909232607,
                'name' => "gholshani",
                'username' => "jainjas",
            ], [
                'nameApi' => "اریا بوکینگ",
                'phone' => 989396162799,
                'name' => "aslani",
                'username' => "ariabooking",
            ], [
                'nameApi' => "گردش",
                'phone' => 989309814241,
                'name' => "teymouri",
                'username' => "egardesh",
            ], [
                'nameApi' => "علادین",
                'phone' => 989107254764,
                'name' => "motalebi",
                'username' => "aladdin",
            ], [
                'nameApi' => "هتل ای ار",
                'phone' => 989332229936,
                'name' => "pakfetrat",
                'username' => "hotel.ir",
            ], [
                'nameApi' => "ایران هتل",
                'phone' => 989309819241,
                'name' => "teymuri",
                'username' => "iranhotel",
            ], [
                'nameApi' => "دلتابان",
                'phone' => 989129579552,
                'name' => "madineh",
                'username' => "deltaban",
            ], [
                'nameApi' => "اقامت 24",
                'phone' => 989036788085,
                'name' => "hojabri",
                'username' => "eghamat24",
            ], [
                'nameApi' => "جاباما",
                'phone' => 989128800300,
                'name' => "احمدی",
                'username' => "jabama",
            ], [
                'nameApi' => "الی گشت",
                'phone' => null,
                'name' => "samaneh moghim",
                'username' => "elleghasht",
            ], [
                'nameApi' => "اسنپ تریپ",
                'phone' => null,
                'name' => "",
                'username' => "snapptrip",
            ],
        ];
        foreach ($data as $value) {
            $api = Api::create([
                'name' => $value['nameApi'],
                'type' => 'percent',
                'percent' => Constants::AGENCY_PERCENT_DEFAULT,
                'status' => Constants::STATUS_ACTIVE,
            ]);
            $refLink = $hashIds->encode($value['phone'], intval(microtime(true)));
            $userApi = User::create([
                'phone' => $value['phone'],
                'name' => $value['name'],
                'username' => $value['username'],
                'password_username' => 202020,
                "ref_link" => $refLink,
            ]);
            Wallet::create([
                'user_id' => $userApi->id,
                'price' => 0,
            ]);
            ApiUser::create([
                'user_id' => $userApi->id,
                'api_id' => $api->id,
                'role' => Constants::ROLE_ADMIN
            ]);
            ApiWallet::create([
                'api_id' => $api->id,
                'price' => 0
            ]);
            ApiApp::create([
                'api_id' => $api->id,
                'app_id' => 2,
            ]);
            //agency
            $agency = Agency::create([
                'name' => $value['nameApi'],
                'image' => '',
                'tell' => '',
                'type' => 'percent',
                'percent' => Constants::AGENCY_PERCENT_DEFAULT,
                'status' => Constants::STATUS_ACTIVE,
                'introduction' => [Constants::AGENCY_INTRODUCTION_SALES]
            ]);
            AgencyUser::create([
                'user_id' => $userApi->id,
                'agency_id' => $agency->id,
                'type' => 'percent',
                'percent' => 100,
                'role' => Constants::ROLE_ADMIN
            ]);
            AgencyWallet::create([
                'agency_id' => $agency->id,
                'price' => 0
            ]);
            AgencyApp::create([
                'agency_id' => $agency->id,
                'app_id' => 2,
            ]);
        }

    }
}
