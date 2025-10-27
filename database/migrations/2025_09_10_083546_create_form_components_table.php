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
        Schema::create('form_components', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // textfield, textarea, select, etc.
            $table->string('label');
            $table->string('label_ar')->nullable();
            $table->string('key')->unique();
            $table->json('properties'); // Component properties
            $table->json('validation')->nullable(); // Validation rules
            $table->json('conditional')->nullable(); // Conditional logic
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('category')->default('basic'); // basic, advanced, layout
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->string('icon')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index(['category', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_components');
    }
};
