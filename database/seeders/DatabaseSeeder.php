<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Run the user type seeder first
        $this->call(UserTypeSeeder::class);
        
        // Run the interest and language seeder
        $this->call(InterestLanguageSeeder::class);
        
        // Add new interests
        $this->call(AddNewInterestsSeeder::class);
        
        // Run the test data seeder
        $this->call(TestDataSeeder::class);
    }
}
