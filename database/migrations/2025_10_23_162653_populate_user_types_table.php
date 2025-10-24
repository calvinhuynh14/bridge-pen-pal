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
        // Insert user types
        DB::table('user_types')->insert([
            [
                'name' => 'admin',
                'requires_email' => true,
                'requires_username' => false,
                'authentication_method' => 'email',
                'description' => 'Administrator users who manage organizations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'volunteer',
                'requires_email' => true,
                'requires_username' => false,
                'authentication_method' => 'email',
                'description' => 'Volunteer users who apply to organizations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'resident',
                'requires_email' => false,
                'requires_username' => true,
                'authentication_method' => 'username',
                'description' => 'Resident users who use username and PIN for login',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_types')->truncate();
    }
};
