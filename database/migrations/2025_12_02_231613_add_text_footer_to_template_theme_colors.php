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
            if (!Schema::hasColumn('template_theme_colors', 'text_footer')) {
                $table->string('text_footer')->default('#ffffff')->after('text_light');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_theme_colors', function (Blueprint $table) {
            if (Schema::hasColumn('template_theme_colors', 'text_footer')) {
                $table->dropColumn('text_footer');
            }
        });
    }
};
