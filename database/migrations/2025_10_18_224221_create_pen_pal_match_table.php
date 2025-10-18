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
            $table->unique(['volunteer_id', 'resident_id']); // One match per volunteer-resident pair
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pen_pal_match');
    }
};
