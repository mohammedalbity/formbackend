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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password'); // admin, user
            $table->string('language')->default('en')->after('role'); // en, ar
            $table->string('timezone')->default('UTC')->after('language');
            $table->json('preferences')->nullable()->after('timezone');
            $table->boolean('is_active')->default(true)->after('preferences');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            
            $table->index(['email', 'is_active']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'is_active']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'language', 
                'timezone',
                'preferences',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};
