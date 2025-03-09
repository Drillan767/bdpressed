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
        DB::statement("
            ALTER TABLE `illustrations`
            CHANGE `status` `status` ENUM(
                'PENDING',
                'ACCEPTED',
                'REJECTED',
                'DEPOSIT_PENDING',
                'DEPOSIT_PAID',
                'PAYMENT_PENDING',
                'PAID',
                'IN_PROGRESS',
                'COMPLETED'
            ) NOT NULL DEFAULT 'PENDING';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
