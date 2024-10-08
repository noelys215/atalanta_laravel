<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->string('category');
            $table->string('department');
            $table->string('brand');
            $table->string('color');
            $table->text('description')->nullable();
            $table->json('inventory');
            $table->json('image');
            $table->string('slug')->nullable(); // Update this line
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('products');
    }
};

