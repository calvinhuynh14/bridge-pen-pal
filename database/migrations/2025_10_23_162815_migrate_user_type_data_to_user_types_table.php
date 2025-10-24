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
        // Check if user_type column exists (for existing data migration)
        if (Schema::hasColumn('users', 'user_type')) {
            // Get user type IDs
            $adminTypeId = DB::table('user_types')->where('name', 'admin')->value('id');
            $volunteerTypeId = DB::table('user_types')->where('name', 'volunteer')->value('id');
            $residentTypeId = DB::table('user_types')->where('name', 'resident')->value('id');

            // Update users with their corresponding user_type_id
            DB::table('users')
                ->where('user_type', 'admin')
                ->update(['user_type_id' => $adminTypeId]);

            DB::table('users')
                ->where('user_type', 'volunteer')
                ->update(['user_type_id' => $volunteerTypeId]);

            DB::table('users')
                ->where('user_type', 'resident')
                ->update(['user_type_id' => $residentTypeId]);
        }
        // If user_type column doesn't exist, this is a fresh migration and no data migration is needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear user_type_id values
        DB::table('users')->update(['user_type_id' => null]);
    }
};
