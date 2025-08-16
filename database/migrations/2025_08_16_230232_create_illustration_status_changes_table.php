<?php

use App\Enums\IllustrationStatus;
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
        Schema::create('illustration_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illustration_id')->constrained()->onDelete('cascade');
            $table->enum('from_status', array_column(IllustrationStatus::cases(), 'value'))->nullable();
            $table->enum('to_status', array_column(IllustrationStatus::cases(), 'value'));
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('triggered_by', ['manual', 'webhook', 'system', 'customer'])->default('manual');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illustration_status_changes');
    }
};
