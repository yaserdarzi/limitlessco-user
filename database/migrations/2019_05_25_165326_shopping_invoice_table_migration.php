<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShoppingInvoiceTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SHOPPING_INVOICE_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id');
            $table->string('shopping_id');
            $table->string('customer_id');
            $table->string('phone')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('count_all')->default(0);
            $table->bigInteger('price_all')->default(0);
            $table->bigInteger('percent_all')->default(0);
            $table->bigInteger('income_all_agency')->default(0);
            $table->bigInteger('income_all_you')->default(0);
            $table->string('code_coupon')->nullable();
            $table->string('type_status');
            $table->string('status');
            $table->string('type');
            $table->string('invoice_status');
            $table->string('payment_token');
            $table->string('ref_id')->nullable();
            $table->string('market');
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::SHOPPING_INVOICE_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::SHOPPING_INVOICE_DB);
    }
}
