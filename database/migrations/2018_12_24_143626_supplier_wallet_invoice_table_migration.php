<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupplierWalletInvoiceTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SUPPLIER_WALLET_INVOICE_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supplier_id');
            $table->bigInteger('wallet_id');
            $table->bigInteger('price_before')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('price_after')->default(0);
            $table->bigInteger('price_all')->default(0);
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
        Schema::table(Constants::SUPPLIER_WALLET_INVOICE_DB, function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on(Constants::SUPPLIER_DB)->onDelete('cascade');
            $table->foreign('wallet_id')->references('id')->on(Constants::WALLET_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::SUPPLIER_WALLET_INVOICE_DB);
    }
}