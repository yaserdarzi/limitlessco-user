<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupplierTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::SUPPLIER_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('image');
            $table->string('tell')->nullable();
            $table->string('type');
            $table->bigInteger('percent')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('award')->default(0);
            $table->bigInteger('income')->default(0);
            $table->string('status')->default(Constants::STATUS_PENDING);
            $table->json('info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::SUPPLIER_DB);
    }
}
