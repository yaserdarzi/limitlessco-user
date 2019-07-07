<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommissionTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::COMMISSION_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_id');
            $table->string('shopping_id');
            $table->string('type')->default(Constants::TYPE_PERCENT);
            $table->bigInteger('percent')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('award')->default(0);
            $table->bigInteger('income')->default(0);
            $table->boolean('is_price_power_up')->default(false);
            $table->json('info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::COMMISSION_DB);
    }
}
