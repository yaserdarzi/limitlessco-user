<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShoppingBagExpireTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SHOPPING_BAG_EXPIRE_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id');
            $table->string('customer_id');
            $table->timestamp('expire_time')->default(date('Y-m-d H:i:s', strtotime("+10 minutes")));
            $table->string('status')->default(Constants::SHOPPING_STATUS_SHOPPING);
            $table->timestamps();
        });
        Schema::table(Constants::SHOPPING_BAG_EXPIRE_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::SHOPPING_BAG_EXPIRE_DB);
    }
}
