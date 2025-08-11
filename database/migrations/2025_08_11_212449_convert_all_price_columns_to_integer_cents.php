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
        // Convert products table - price to integer (cents)
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price')->change();
        });

        // Convert orders table - all amounts to integers (cents)
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('total')->change();
            $table->integer('shipmentFees')->change();
            $table->integer('stripeFees')->change();
        });

        // Convert order_details table - price to integer (cents)
        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('price')->change();
        });

        // Convert illustrations table - price to integer (cents)
        Schema::table('illustrations', function (Blueprint $table) {
            $table->integer('price')->change();
        });

        // Convert illustration_prices table - price to integer (cents)
        Schema::table('illustration_prices', function (Blueprint $table) {
            $table->integer('price')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original types
        Schema::table('products', function (Blueprint $table) {
            $table->float('price', 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->float('total', 2)->change();
            $table->float('shipmentFees', 2)->change();
            $table->float('stripeFees', 2)->change();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->float('price', 2)->change();
        });

        Schema::table('illustrations', function (Blueprint $table) {
            $table->float('price', 2)->change();
        });

        Schema::table('illustration_prices', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }
};
