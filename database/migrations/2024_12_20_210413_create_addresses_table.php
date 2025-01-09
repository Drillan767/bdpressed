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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('street');
            $table->string('street2')->default('');
            $table->string('city');
            $table->string('zipCode');
            $table->string('country');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained();
            $table->foreignId('guest_id')
                ->nullable()
                ->constrained();
            $table->enum('type', ['BILLING', 'SHIPPING']);
            $table->boolean('default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
