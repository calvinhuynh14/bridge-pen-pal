<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class PinGenerationService
{
    /**
     * Generate a secure 6-digit PIN
     * Avoids common patterns like 000000, 123456, etc.
     */
    public function generatePin(): string
    {
        do {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->isWeakPin($pin));
        
        return $pin;
    }
    
    /**
     * Check if PIN is weak and should be regenerated
     */
    private function isWeakPin(string $pin): bool
    {
        $weakPins = [
            '000000', '111111', '222222', '333333', '444444', '555555',
            '666666', '777777', '888888', '999999', '123456', '654321',
            '012345', '543210', '111111', '222222', '333333', '444444',
            '555555', '666666', '777777', '888888', '999999'
        ];
        
        return in_array($pin, $weakPins);
    }
    
    /**
     * Hash a PIN for storage
     */
    public function hashPin(string $pin): string
    {
        return Hash::make($pin);
    }
    
    /**
     * Generate multiple PINs for batch creation
     */
    public function generateBatchPins(int $count): array
    {
        $pins = [];
        $usedPins = [];
        
        for ($i = 0; $i < $count; $i++) {
            do {
                $pin = $this->generatePin();
            } while (in_array($pin, $usedPins));
            
            $pins[] = $pin;
            $usedPins[] = $pin;
        }
        
        return $pins;
    }
}
