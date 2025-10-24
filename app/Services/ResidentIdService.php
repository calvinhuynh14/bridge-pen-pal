<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ResidentIdService
{
    /**
     * Generate the next available resident ID
     * Format: 6-digit number starting from 100000
     */
    public function generateNextId(): string
    {
        // Get the highest existing resident ID
        $lastId = DB::table('users')
            ->where('username', 'REGEXP', '^[0-9]{6}$')
            ->orderBy('username', 'desc')
            ->value('username');
        
        // Start from 100000 if no existing IDs
        $nextNumber = $lastId ? (int)$lastId + 1 : 100000;
        
        // Ensure it's 6 digits
        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate multiple resident IDs for batch creation
     */
    public function generateBatchIds(int $count): array
    {
        $ids = [];
        $currentId = $this->generateNextId();
        
        for ($i = 0; $i < $count; $i++) {
            $ids[] = $currentId;
            $currentId = str_pad((int)$currentId + 1, 6, '0', STR_PAD_LEFT);
        }
        
        return $ids;
    }
    
    /**
     * Check if a resident ID is available
     */
    public function isIdAvailable(string $id): bool
    {
        return !DB::table('users')
            ->where('username', $id)
            ->exists();
    }
    
    /**
     * Get the next available ID after checking for gaps
     */
    public function getNextAvailableId(): string
    {
        $lastId = DB::table('users')
            ->where('username', 'REGEXP', '^[0-9]{6}$')
            ->orderBy('username', 'desc')
            ->value('username');
        
        if (!$lastId) {
            return '100000';
        }
        
        $nextNumber = (int)$lastId + 1;
        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
