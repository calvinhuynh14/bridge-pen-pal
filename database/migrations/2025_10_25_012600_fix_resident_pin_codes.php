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
        // For existing residents, we need to generate new PIN codes
        // since we can't reverse the hashed passwords
        $residents = DB::table('resident')
            ->join('users', 'resident.user_id', '=', 'users.id')
            ->whereNotNull('resident.pin_code')
            ->select('resident.id', 'resident.pin_code')
            ->get();

        foreach ($residents as $resident) {
            // Generate a new 6-digit PIN code
            $newPin = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Update the resident record with the plain PIN
            DB::table('resident')
                ->where('id', $resident->id)
                ->update(['pin_code' => $newPin]);
            
            // Update the user's password with the hashed version of the new PIN
            DB::table('users')
                ->where('id', DB::table('resident')->where('id', $resident->id)->value('user_id'))
                ->update(['password' => bcrypt($newPin)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible
        // as we can't restore the original hashed PIN codes
    }
};