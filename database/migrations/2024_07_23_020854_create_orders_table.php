<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->json('order_items');
            $table->json('shipping_address');
            $table->string('payment_method');
            $table->decimal('items_price', 8, 2);
            $table->decimal('tax_price', 8, 2);
            $table->decimal('shipping_price', 8, 2);
            $table->decimal('total_price', 8, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->json('payment_result')->nullable();
            $table->boolean('is_shipped')->default(false);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};

