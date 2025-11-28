<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interest;

class AddNewInterestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // New interests to add (excluding ones that already exist)
        $newInterests = [
            'Art',
            'Beauty',
            'Makeup',
            'Fashion',
            'Poetry',
            'Books',
            'Space',
            'Sci‑fi',
            'Fantasy',
            'Anime',
            'Comics',
            'Classic films',
            'TV shows',
            'Singing',
            'Gaming',
            'Video games',
            'Board games',
            'Martial arts',
            'Fitness',
            'Yoga',
            'Walking',
            'Nature',
            'Animals',
            'Pets',
            'Environment',
            'Climate',
            'Technology',
            'Computers',
            'Programming',
            'Food',
            'Coffee',
            'Tea',
            'Cars',
            'Motorcycles',
            'Magic',
            'Casual chat',
            'Education',
            'Teaching',
            'Learning',
            'Politics',
            'News',
            'Finance',
            'Investing',
            'Business',
            'Entrepreneurship',
            'Religion',
            'Spirituality',
            'Family',
            'Parenting',
            'Relationships',
            'Disabilities',
            'Deaf community',
            'Mental health',
            'Self‑care',
            'Journaling',
            'Volunteering',
            'Community',
            'Culture',
            'Sports',
        ];
        
        // Add interests (only if they don't already exist)
        foreach ($newInterests as $interestName) {
            Interest::firstOrCreate(
                ['name' => $interestName]
            );
        }
        
        $this->command->info('New interests added successfully!');
    }
}


