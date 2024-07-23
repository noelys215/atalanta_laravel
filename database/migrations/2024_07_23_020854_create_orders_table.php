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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('order_items');
            $table->json('shipping_address');
            $table->decimal('items_price', 8, 2);
            $table->string('payment_method');
            $table->json('payment_result')->nullable();
            $table->decimal('tax_price', 8, 2)->default(0.0);
            $table->decimal('shipping_price', 8, 2)->default(0.0);
            $table->decimal('total_price', 8, 2)->default(0.0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_shipped')->default(false);
            $table->timestamp('shipped_at')->nullable();
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
