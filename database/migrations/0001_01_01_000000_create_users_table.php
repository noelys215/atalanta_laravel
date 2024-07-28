<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->default('John');
            $table->string('last_name')->default('Doe');
            $table->string('email')->unique()->default('john@atalanta.com');
            $table->string('telephone')->default(' ');
            $table->string('country')->default(' ');
            $table->string('address')->default(' ');
            $table->string('address_cont')->nullable();
            $table->string('state')->default(' ');
            $table->string('city')->default('NJ');
            $table->string('postal_code')->default('08110');
            $table->string('password');
            $table->boolean('is_admin')->default(false);
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
};

