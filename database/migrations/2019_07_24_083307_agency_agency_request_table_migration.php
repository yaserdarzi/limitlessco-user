<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgencyAgencyRequestTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::AGENCY_AGENCY_REQUEST_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('agency_id');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('fax')->nullable();
            $table->string('web')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default(Constants::STATUS_PENDING);
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::AGENCY_AGENCY_REQUEST_DB, function (Blueprint $table) {
            $table->foreign('agency_id')->references('id')->on(Constants::AGENCY_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::AGENCY_AGENCY_REQUEST_DB);
    }


}
