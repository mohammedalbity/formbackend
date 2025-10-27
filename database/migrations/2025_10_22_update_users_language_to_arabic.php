<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all users with null or 'en' language to 'ar' (Arabic)
        DB::table('users')
            ->whereIn('language', ['en', null])
            ->orWhere('language', '')
            ->update(['language' => 'ar']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert back to English
        DB::table('users')
            ->where('language', 'ar')
            ->update(['language' => 'en']);
    }
};
