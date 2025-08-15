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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('illustration_id')->nullable()->constrained();
            $table->enum('type', ['order_full', 'illustration_deposit', 'illustration_final']);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded']);
            $table->integer('amount');
            $table->string('currency', 3)->default('EUR');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_payment_link')->nullable();
            $table->integer('stripe_fee')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->integer('refunded_amount')->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->json('stripe_metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
