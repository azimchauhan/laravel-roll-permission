<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->integer('inventory_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->float('selling_price', 8, 2)->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->float('total_amount', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};
