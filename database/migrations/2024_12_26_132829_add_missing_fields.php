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
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street2')->nullable();
            $table->dropColumn('phone');
        });


        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('billing_address_id');
            $table->dropForeign('shipping_address_id');

            $table->dropColumn('billing_address_id');                
            $table->dropColumn('shipping_address_id');

            $table->foreignId('billing_address_id')->constrained('users', 'id', 'billing_address_id');
            $table->foreignId('shipping_address_id')->constrained('users', 'id', 'shipping_address_id');
            $table->enum('type', ['BILLING', 'SHIPPING']);
            $table->boolean('default')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('street2');
            $table->string('phone')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('default');

            $table->dropForeign('billing_address_id');
            $table->dropForeign('shipping_address_id');

            $table->dropColumn('billing_address_id');                
            $table->dropColumn('shipping_address_id');

            $table->foreignId('billing_address_id')->constrained('orders', 'id', 'billing_address_id');
            $table->foreignId('shipping_address_id')->constrained('orders', 'id', 'shipping_address_id');
        });
    }
};
