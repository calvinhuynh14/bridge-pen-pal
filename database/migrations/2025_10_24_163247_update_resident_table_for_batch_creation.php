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
            $table->string('room_number', 10)->nullable();
            $table->string('floor_number', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('pin_code')->nullable(); // Hashed PIN
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident', function (Blueprint $table) {
            $table->dropColumn(['room_number', 'floor_number', 'date_of_birth', 'pin_code']);
        });
    }
};
