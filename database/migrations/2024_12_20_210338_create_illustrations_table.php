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
        Schema::create('illustrations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['BUST', 'FULL_LENGTH', 'ANIMAL']);
            $table->integer('nbHumans')->default(1);
            $table->integer('nbAnimals')->default(0);
            $table->integer('nbItems')->default(0);
            $table->enum('pose', ['SIMPLE', 'COMPLEX']);
            $table->enum('background', ['UNIFIED', 'GRADIENT', 'SIMPLE', 'COMPLEX']);
            $table->float('price', 2);
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED']);
            $table->text('description');
            $table->foreignId('order_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illustrations');
    }
};
