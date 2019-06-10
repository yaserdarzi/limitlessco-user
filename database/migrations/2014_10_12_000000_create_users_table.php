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
            $table->string('password')->nullable();
            $table->string('gmail')->nullable();
            $table->string('username')->nullable();
            $table->string('password_username')->nullable();
            $table->rememberToken()->nullable();
            $table->string('name');
            $table->string('tell')->nullable();
            $table->string('image');
            $table->string('gender');
            $table->string('ref_link')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['phone', 'email', 'gmail', 'username']);
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
