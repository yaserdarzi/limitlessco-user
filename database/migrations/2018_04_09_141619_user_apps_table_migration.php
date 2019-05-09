<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserAppsTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::USERS_APPS_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('app_id');
            $table->boolean('activated')->default(true);
            $table->integer('created_at');
        });
        Schema::table(Constants::USERS_APPS_DB, function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on(Constants::USERS_DB)->onDelete('cascade');
            $table->foreign('app_id')->references('id')->on(Constants::APP_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::USERS_APPS_DB);
    }
}
