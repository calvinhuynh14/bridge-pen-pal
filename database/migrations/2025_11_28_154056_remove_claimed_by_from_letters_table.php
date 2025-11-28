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
        Schema::table('letters', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['claimed_by']);
            // Drop the column
            $table->dropColumn('claimed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Add the column back
            $table->foreignId('claimed_by')->nullable()->constrained('users')->onDelete('set null')->after('receiver_id');
        });
    }
};
