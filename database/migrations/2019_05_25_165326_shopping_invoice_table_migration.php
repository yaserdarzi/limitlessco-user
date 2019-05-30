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
            $table->string('customer_id');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->bigInteger('count_all')->default(0);
            $table->bigInteger('price_all')->default(0);
            $table->bigInteger('percent_all')->default(0);
            $table->bigInteger('income_all')->default(0);
            $table->bigInteger('income_you')->default(0);
            $table->bigInteger('price_payment')->default(0);
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
