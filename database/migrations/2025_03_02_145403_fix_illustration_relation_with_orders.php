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
        Schema::table('illustrations', function (Blueprint $table) {
            $table->dropForeign('illustrations_order_detail_id_foreign');
            $table->dropColumn('order_detail_id');

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->dropColumn('nbItems');
            $table->integer('nbHumans')->default(0)->change();
            $table->boolean('addTracking')->default(false);
            $table->boolean('print')->default(false);
            $table->string('trackingNumber')->nullable();
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
