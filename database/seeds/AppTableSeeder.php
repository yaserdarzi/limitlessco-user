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
        \App\App::create([
            'app' => 'entertainment',
            'country' => 'IR',
            'is_supplier' => true,
            'is_agency' => true
        ]);
        \App\App::create([
            'app' => 'hotel',
            'country' => 'IR',
            'is_supplier' => true,
            'is_agency' => true
        ]);
    }
}
