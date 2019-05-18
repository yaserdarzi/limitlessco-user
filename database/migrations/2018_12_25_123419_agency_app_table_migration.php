<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgencyAppTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::AGENCY_API_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('agency_id');
            $table->bigInteger('app_id');
            $table->json('info');
        });
        Schema::table(Constants::AGENCY_API_DB, function (Blueprint $table) {
            $table->foreign('agency_id')->references('id')->on(Constants::AGENCY_DB)->onDelete('cascade');
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
        Schema::dropIfExists(Constants::AGENCY_API_DB);
    }
}
