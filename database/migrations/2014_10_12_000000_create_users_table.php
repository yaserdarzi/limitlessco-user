<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::USERS_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('gmail')->nullable();
            $table->rememberToken()->nullable();
            $table->string('name');
            $table->string('image');
            $table->string('gender');
            $table->string('ref_link')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['phone', 'email', 'gmail']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::USERS_DB);
    }
}
