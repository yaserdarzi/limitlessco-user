<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::APP_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('app');
            $table->string('type_app');
            $table->string('type_app_child');
            $table->bigInteger('cash_back');
            $table->json('info');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::APP_DB);
    }
}
