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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->float('total', 2);
            $table->string('reference');
            $table->text('additionalInfos')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('useSameAddress')->default(true);
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
                'DONE',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
