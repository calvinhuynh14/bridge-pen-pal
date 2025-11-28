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
        Schema::table('interests', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['category_id']);
            // Drop the column
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: interest_categories table must exist for this to work
        Schema::table('interests', function (Blueprint $table) {
            // Add the column back
            $table->foreignId('category_id')->nullable()->constrained('interest_categories')->after('name');
        });
    }
};
