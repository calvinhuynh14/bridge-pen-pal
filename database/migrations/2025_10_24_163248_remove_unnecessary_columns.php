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
        Schema::table('resident', function (Blueprint $table) {
            $table->dropColumn(['application_notes', 'medical_notes']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('current_team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident', function (Blueprint $table) {
            $table->text('application_notes')->nullable();
            $table->text('medical_notes')->nullable();
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable();
        });
    }
};
