<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApiSettingTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::API_SETTING_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('api_id');
            $table->string('type_payment');
            $table->json('info_payment');
            $table->string('type_sms');
            $table->json('info_sms');
            $table->json('info');
            $table->timestamps();
        });
        Schema::table(Constants::API_SETTING_DB, function (Blueprint $table) {
            $table->foreign('api_id')->references('id')->on(Constants::API_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::API_SETTING_DB);
    }
}
