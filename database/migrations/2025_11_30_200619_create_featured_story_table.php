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
        Schema::create('featured_story', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('resident_id'); // The resident being featured
            $table->text('bio'); // The bio/story about the resident
            $table->timestamps();
            
            $table->foreign('organization_id')->references('id')->on('organization')->onDelete('cascade');
            $table->foreign('resident_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('organization_id'); // One featured story per organization
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_story');
    }
};
