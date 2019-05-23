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
        \App\App::create([
            'title' => 'گردشگری',
            'app' => 'entertainment',
            'country' => 'IR',
            'is_supplier' => true,
            'is_agency' => true,
            'is_api' => true
        ]);
        \App\App::create([
            'title' => 'هتل',
            'app' => 'hotel',
            'country' => 'IR',
            'is_supplier' => true,
            'is_agency' => true,
            'is_api' => true
        ]);
    }
}
