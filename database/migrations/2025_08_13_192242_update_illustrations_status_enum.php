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
        // Update illustrations status enum
        Schema::table('illustrations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('illustrations', function (Blueprint $table) {
            $table->enum('status', [
                'PENDING',
                'DEPOSIT_PENDING', 
                'DEPOSIT_PAID',
                'IN_PROGRESS',
                'CLIENT_REVIEW',
                'PAYMENT_PENDING',
                'COMPLETED',
                'CANCELLED'
            ])->default('PENDING');
        });

        // Update orders status enum
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'NEW',
                'IN_PROGRESS',
                'PENDING_PAYMENT',
                'PAID',
                'TO_SHIP',
                'SHIPPED',
                'DONE',
                'CANCELLED'
            ])->default('NEW');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore illustrations status enum
        Schema::table('illustrations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('illustrations', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED'])->default('PENDING');
        });

        // Restore orders status enum
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'CANCELLED',
                'NEW',
                'ILLUSTRATION_DEPOSIT_PENDING',
                'ILLUSTRATION_DEPOSIT_PAID',
                'PENDING_CLIENT_REVIEW',
                'IN_PROGRESS',
                'PAYMENT_PENDING',
                'PAID',
                'TO_SHIP',
                'SHIPPED',
                'DONE',
            ])->default('NEW');
        });
    }
};
