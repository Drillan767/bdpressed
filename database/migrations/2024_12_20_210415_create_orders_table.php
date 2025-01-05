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
            $table->float('shipmentFees', 2);
            $table->float('stripeFees', 2);
            $table->string('reference');
            $table->text('additionalInfos')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');
            $table->foreignId('guest_id')
                ->nullable()
                ->constrained('guests');
            $table->foreignId('shipping_address_id')
                ->constrained('addresses', 'id');
            $table->foreignId('billing_address_id')
                ->constrained('addresses', 'id');

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
                'SHIPPED',
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
