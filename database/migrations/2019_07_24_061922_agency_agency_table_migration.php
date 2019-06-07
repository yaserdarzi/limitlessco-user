<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgencyAgencyTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::AGENCY_AGENCY_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('agency_parent_id');
            $table->bigInteger('agency_agency_category_id');
            $table->bigInteger('agency_id');
            $table->bigInteger('capacity_percent')->default(0);
            $table->string('type_price')->default(Constants::TYPE_PERCENT);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('percent')->default(0);
            $table->bigInteger('award')->default(0);
            $table->string('status')->default(Constants::STATUS_ACTIVE);
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::AGENCY_AGENCY_DB, function (Blueprint $table) {
            $table->foreign('agency_parent_id')->references('id')->on(Constants::AGENCY_DB)->onDelete('cascade');
            $table->foreign('agency_agency_category_id')->references('id')->on(Constants::AGENCY_AGENCY_CATEGORY_DB)->onDelete('cascade');
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
        Schema::dropIfExists(Constants::AGENCY_AGENCY_DB);
    }
}