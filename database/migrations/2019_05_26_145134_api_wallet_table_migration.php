<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApiWalletTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::API_WALLET_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('api_id');
            $table->bigInteger('price');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table(Constants::API_WALLET_DB, function (Blueprint $table) {
            $table->foreign('api_id')->references('id')->on(Constants::API_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::API_WALLET_DB);
    }
}
