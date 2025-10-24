<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user types already exist
        if (DB::table('user_types')->count() === 0) {
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
    }
}
