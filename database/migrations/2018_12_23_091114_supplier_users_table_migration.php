<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupplierUsersTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SUPPLIER_USERS_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('supplier_id');
            $table->string('type');
            $table->bigInteger('percent')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('income')->default(0);
            $table->bigInteger('award')->default(0);
            $table->string('role')->default(Constants::ROLE_COUNTER_MAN);
            $table->timestamps();
        });
        Schema::table(Constants::SUPPLIER_USERS_DB, function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on(Constants::USERS_DB)->onDelete('cascade');
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
        Schema::dropIfExists(Constants::SUPPLIER_USERS_DB);
    }
}
