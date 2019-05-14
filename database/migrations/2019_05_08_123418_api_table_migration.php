<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApiTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::API_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('type');
            $table->bigInteger('percent')->nullable()->default(0);
            $table->bigInteger('price')->nullable()->default(0);
            $table->bigInteger('income')->nullable()->default(0);
            $table->bigInteger('award')->nullable()->default(0);
            $table->json('info');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table(Constants::API_DB, function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on(Constants::USERS_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::API_DB);
    }
}
