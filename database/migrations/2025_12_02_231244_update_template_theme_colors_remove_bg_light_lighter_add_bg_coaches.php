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
        Schema::table('template_theme_colors', function (Blueprint $table) {
            // Remove bg_light and bg_lighter columns if they exist
            if (Schema::hasColumn('template_theme_colors', 'bg_light')) {
                $table->dropColumn('bg_light');
            }
            if (Schema::hasColumn('template_theme_colors', 'bg_lighter')) {
                $table->dropColumn('bg_lighter');
            }
            
            // Add bg_coaches column if it doesn't exist
            if (!Schema::hasColumn('template_theme_colors', 'bg_coaches')) {
                $table->string('bg_coaches')->default('#f8f9fa')->after('bg_packages');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_theme_colors', function (Blueprint $table) {
            // Remove bg_coaches
            if (Schema::hasColumn('template_theme_colors', 'bg_coaches')) {
                $table->dropColumn('bg_coaches');
            }
            
            // Restore bg_light and bg_lighter
            if (!Schema::hasColumn('template_theme_colors', 'bg_light')) {
                $table->string('bg_light')->default('#f8f9fa')->after('bg_white');
            }
            if (!Schema::hasColumn('template_theme_colors', 'bg_lighter')) {
                $table->string('bg_lighter')->default('#e9ecef')->after('bg_light');
            }
        });
    }
};
