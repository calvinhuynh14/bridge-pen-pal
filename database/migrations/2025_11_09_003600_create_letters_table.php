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
        Schema::create('letters', function (Blueprint $table) {
            $table->id(); // Primary key (auto-incrementing BIGINT UNSIGNED)
            
            // Foreign keys
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('claimed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Letter properties
            $table->text('content'); // Letter content (max 1000 characters enforced at application level)
            $table->boolean('is_open_letter')->default(false); // Is this an open letter?
            
            // Threading (for replies)
            $table->foreignId('parent_letter_id')->nullable()->constrained('letters')->onDelete('cascade');
            
            // Status tracking
            $table->enum('status', ['draft', 'sent', 'delivered', 'read', 'archived'])->default('draft');
            
            // Timestamps for delivery and reading
            $table->timestamp('sent_at')->nullable(); // When letter was sent
            $table->timestamp('delivered_at')->nullable(); // When letter was delivered (8 hours after sent)
            $table->timestamp('read_at')->nullable(); // When recipient read the letter
            
            // Laravel timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft deletes (adds deleted_at column)
            
            // Indexes for performance
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('is_open_letter');
            $table->index('status');
            $table->index('delivered_at');
            $table->index('parent_letter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};

