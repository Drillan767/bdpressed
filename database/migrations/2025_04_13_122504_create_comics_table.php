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
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('preview');
            $table->boolean('is_published')->default(false);
            $table->string('instagram_url');
            $table->timestamps();
        });

        Schema::create('comic_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained();
            $table->string('image');
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comics');
        Schema::dropIfExists('comic_pages');
    }
};
