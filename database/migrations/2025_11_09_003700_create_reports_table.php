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
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // Primary key (auto-incrementing BIGINT UNSIGNED)
            
            // Foreign keys
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade'); // Who reported
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade'); // Reported user, if applicable
            $table->foreignId('reported_letter_id')->nullable()->constrained('letters')->onDelete('cascade'); // Reported letter, if applicable
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who resolved
            
            // Report details
            $table->text('reason'); // Report reason
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable(); // Admin's notes on the report
            
            // Resolution tracking
            $table->timestamp('resolved_at')->nullable(); // When report was resolved
            
            // Laravel timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('reporter_id');
            $table->index('reported_user_id');
            $table->index('reported_letter_id');
            $table->index('status');
            $table->index('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

