<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street2')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
