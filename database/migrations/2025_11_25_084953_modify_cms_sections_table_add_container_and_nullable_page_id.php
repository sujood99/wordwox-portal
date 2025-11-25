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
        Schema::table('cms_sections', function (Blueprint $table) {
            // Make cms_page_id nullable for footer sections
            $table->unsignedBigInteger('cms_page_id')->nullable()->change();
            
            // Add container field
            $table->string('container')->nullable()->after('css_classes');
            
            // Add index for container and footer queries
            $table->index('container');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_sections', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['container']);
            
            // Drop container column
            $table->dropColumn('container');
            
            // Revert cms_page_id to not nullable
            $table->unsignedBigInteger('cms_page_id')->nullable(false)->change();
        });
    }
};
