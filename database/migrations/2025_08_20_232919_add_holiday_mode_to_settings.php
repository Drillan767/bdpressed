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
        // Add holiday_mode setting with default value false
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            'group' => 'website',
            'name' => 'holiday_mode',
            'payload' => json_encode(false),
            'locked' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('group', 'website')
            ->where('name', 'holiday_mode')
            ->delete();
    }
};
