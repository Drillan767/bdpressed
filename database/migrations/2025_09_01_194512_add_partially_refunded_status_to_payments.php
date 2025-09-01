<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include the new partially_refunded status
        DB::statement("ALTER TABLE order_payments MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the partially_refunded status from the enum
        DB::statement("ALTER TABLE order_payments MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL");
    }
};
