<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShoppingBagTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SHOPPING_BAG_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id');
            $table->string('shopping_id');
            $table->string('customer_id');
            $table->string('title');
            $table->string('title_more')->nullable();
            $table->timestamp('date');
            $table->timestamp('date_end')->nullable();
            $table->string('start_hours')->nullable();
            $table->string('end_hours')->nullable();
            $table->bigInteger('price_fee')->default(0);
            $table->bigInteger('percent_fee')->default(0);
            $table->bigInteger('count')->default(1);
            $table->bigInteger('price_all')->default(0);
            $table->bigInteger('percent_all')->default(0);
            $table->bigInteger('income_agency')->default(0);
            $table->bigInteger('income_you')->default(0);
            $table->json('shopping')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::SHOPPING_BAG_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::SHOPPING_BAG_DB);
    }
}
