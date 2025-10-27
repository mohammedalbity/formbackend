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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('schema'); // Form.io schema
            $table->json('display')->nullable(); // Form.io display settings
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('language')->default('en'); // en, ar
            $table->boolean('is_public')->default(false);
            $table->boolean('allow_anonymous')->default(true);
            $table->json('settings')->nullable(); // Additional form settings
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['status', 'is_public']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
