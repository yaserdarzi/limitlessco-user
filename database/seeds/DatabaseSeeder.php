<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AppTableSeeder::class);
        $this->call(SalesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }
}
