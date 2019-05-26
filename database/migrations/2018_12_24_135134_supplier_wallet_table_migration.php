<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupplierWalletTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SUPPLIER_WALLET_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supplier_id');
            $table->bigInteger('price');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table(Constants::SUPPLIER_WALLET_DB, function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on(Constants::SUPPLIER_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::SUPPLIER_WALLET_DB);
    }
}
