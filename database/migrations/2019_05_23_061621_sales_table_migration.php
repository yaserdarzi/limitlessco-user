<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SalesTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SALES_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id');
            $table->string('title');
            $table->string('logo');
            $table->string('type');
            $table->bigInteger('count_sellers')->default(1000);
            $table->json('info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table(Constants::SALES_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::SALES_DB);
    }
}
