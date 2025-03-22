<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('illustration_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('key')->unique();
            $table->decimal('price', 10, 2);
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('illustration_prices');
    }
}; 