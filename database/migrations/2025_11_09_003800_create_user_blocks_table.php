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
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id(); // Primary key (auto-incrementing BIGINT UNSIGNED)
            
            // Foreign keys
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade'); // Who is blocking
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade'); // Who is blocked
            
            // Laravel timestamps
            $table->timestamps();
            
            // Unique constraint to prevent duplicate blocks
            $table->unique(['blocker_id', 'blocked_id']);
            
            // Indexes for performance
            $table->index('blocker_id');
            $table->index('blocked_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};

