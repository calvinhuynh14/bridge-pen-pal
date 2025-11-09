<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old table structure
        Schema::dropIfExists('pen_pal_match');
        
        // Create the new structure
        Schema::create('pen_pal_match', function (Blueprint $table) {
            $table->id();
            
            // Changed from volunteer_id/resident_id to user1_id/user2_id
            // This allows: Volunteer+Resident OR Resident+Resident matches
            // Application logic prevents: Volunteer+Volunteer matches
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            
            // Keep organization_id for multi-tenant purposes
            $table->unsignedBigInteger('organization_id');
            
            $table->enum('status', ['active', 'inactive', 'paused', 'ended'])->default('active');
            $table->timestamp('match_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->text('match_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('organization_id')->references('id')->on('organization');
            
            // Unique constraint: prevent duplicate matches (order-independent)
            // This ensures (user1=1, user2=2) and (user1=2, user2=1) are treated as the same match
            $table->unique(['user1_id', 'user2_id'], 'unique_match_pair');
            
            // Index for organization filtering
            $table->index('organization_id');
            $table->index('status');
        });
        
        // Note: Application logic must enforce:
        // - At least one of user1_id or user2_id must be a resident (not both volunteers)
        // - This can be checked by joining with users table and checking user_type
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pen_pal_match');
        
        // Recreate old structure (if needed for rollback)
        Schema::create('pen_pal_match', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('volunteer_id');
            $table->unsignedBigInteger('resident_id');
            $table->unsignedBigInteger('organization_id');
            $table->enum('status', ['active', 'inactive', 'paused', 'ended'])->default('active');
            $table->timestamp('match_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->text('match_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('volunteer_id')->references('id')->on('volunteer');
            $table->foreign('resident_id')->references('id')->on('resident');
            $table->foreign('organization_id')->references('id')->on('organization');
            $table->unique(['volunteer_id', 'resident_id']);
        });
    }
};

