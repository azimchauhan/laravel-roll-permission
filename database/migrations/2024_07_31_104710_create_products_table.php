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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->longText('details')->nullable();
            $table->float('purchase_price', 8, 2);
            $table->float('selling_price', 8, 2);
            $table->integer('status')->default(1)->comment('0 => InActive 1 => Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
