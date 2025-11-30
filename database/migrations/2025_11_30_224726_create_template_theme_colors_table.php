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
        if (Schema::hasTable('template_theme_colors')) {
            Schema::dropIfExists('template_theme_colors');
        }
        
        Schema::create('template_theme_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id');
            $table->string('template')->default('fitness'); // template name (fitness, modern, etc.)
            
            // Primary Brand Colors
            $table->string('primary_color')->default('#ff6b6b');
            $table->string('secondary_color')->default('#4ecdc4');
            
            // Text Colors
            $table->string('text_dark')->default('#2c3e50');
            $table->string('text_gray')->default('#6c757d');
            $table->string('text_base')->default('#333');
            $table->string('text_light')->default('#ffffff');
            
            // Background Colors
            $table->string('bg_white')->default('#ffffff');
            $table->string('bg_light')->default('#f8f9fa');
            $table->string('bg_lighter')->default('#e9ecef');
            $table->string('bg_packages')->default('#f2f4f6');
            $table->string('bg_footer')->default('#2c3e50');
            
            // Interactive Colors
            $table->string('primary_hover')->default('#ff5252');
            $table->string('secondary_hover')->default('#3db8a8');
            
            $table->timestamps();
            
            // Unique constraint: one theme per org per template
            $table->unique(['org_id', 'template']);
            
            // Foreign key - Note: org table uses bigint unsigned, so we match that
            // Using unsignedBigInteger to match org.id type
            
            // Indexes
            $table->index('org_id');
            $table->index('template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_theme_colors');
    }
};
