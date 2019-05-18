<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgencyUsersTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::AGENCY_USERS_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('agency_id');
            $table->string('type');
            $table->bigInteger('percent')->default(0);
            $table->bigInteger('price')->default(0);
            $table->bigInteger('income')->default(0);
            $table->bigInteger('award')->default(0);
            $table->string('role')->default(Constants::ROLE_COUNTER_MAN);
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::AGENCY_USERS_DB, function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on(Constants::USERS_DB)->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on(Constants::AGENCY_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::AGENCY_USERS_DB);
    }
}
