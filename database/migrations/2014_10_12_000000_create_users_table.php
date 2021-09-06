<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::CREATE('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('role', ['ATTENDANT', 'SUPERVISOR', 'DIRECTOR'])->default('ATTENDANT');
            $table->boolean('active')->nullable()->default(true);
            $table->string('email')->unique();
            $table->string('phonenumber')->unique();
            $table->string('api_token')->nullable();
            $table->integer('store_id')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
