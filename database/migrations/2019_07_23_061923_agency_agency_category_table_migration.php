<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgencyAgencyCategoryTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::AGENCY_AGENCY_CATEGORY_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('agency_id');
            $table->string('title');
            $table->string('type_price')->default(Constants::TYPE_PERCENT);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('percent')->default(0);
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::AGENCY_AGENCY_CATEGORY_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::AGENCY_AGENCY_CATEGORY_DB);
    }
}