<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interest;
use App\Models\InterestCategory;

class AddNewInterestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create categories
        $artsCategory = InterestCategory::firstOrCreate(
            ['name' => 'Arts & Crafts'],
            ['description' => 'Creative and artistic pursuits']
        );
        
        $hobbiesCategory = InterestCategory::firstOrCreate(
            ['name' => 'Hobbies & Interests'],
            ['description' => 'Personal hobbies and interests']
        );
        
        $learningCategory = InterestCategory::firstOrCreate(
            ['name' => 'Learning & Culture'],
            ['description' => 'Educational and cultural activities']
        );
        
        $sportsCategory = InterestCategory::firstOrCreate(
            ['name' => 'Sports & Recreation'],
            ['description' => 'Physical activities and sports']
        );
        
        // New interests to add (excluding ones that already exist)
        $newInterests = [
            // Arts & Crafts
            ['name' => 'Art', 'category_id' => $artsCategory->id],
            ['name' => 'Beauty', 'category_id' => $artsCategory->id],
            ['name' => 'Makeup', 'category_id' => $artsCategory->id],
            ['name' => 'Fashion', 'category_id' => $artsCategory->id],
            ['name' => 'Poetry', 'category_id' => $artsCategory->id],
            
            // Hobbies & Interests
            ['name' => 'Books', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Space', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Sci‑fi', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Fantasy', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Anime', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Comics', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Classic films', 'category_id' => $hobbiesCategory->id],
            ['name' => 'TV shows', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Singing', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Gaming', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Video games', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Board games', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Martial arts', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Fitness', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Yoga', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Walking', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Nature', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Animals', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Pets', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Environment', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Climate', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Technology', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Computers', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Programming', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Food', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Coffee', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Tea', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Cars', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Motorcycles', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Magic', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Casual chat', 'category_id' => $hobbiesCategory->id],
            
            // Learning & Culture
            ['name' => 'Education', 'category_id' => $learningCategory->id],
            ['name' => 'Teaching', 'category_id' => $learningCategory->id],
            ['name' => 'Learning', 'category_id' => $learningCategory->id],
            ['name' => 'Politics', 'category_id' => $learningCategory->id],
            ['name' => 'News', 'category_id' => $learningCategory->id],
            ['name' => 'Finance', 'category_id' => $learningCategory->id],
            ['name' => 'Investing', 'category_id' => $learningCategory->id],
            ['name' => 'Business', 'category_id' => $learningCategory->id],
            ['name' => 'Entrepreneurship', 'category_id' => $learningCategory->id],
            ['name' => 'Religion', 'category_id' => $learningCategory->id],
            ['name' => 'Spirituality', 'category_id' => $learningCategory->id],
            ['name' => 'Family', 'category_id' => $learningCategory->id],
            ['name' => 'Parenting', 'category_id' => $learningCategory->id],
            ['name' => 'Relationships', 'category_id' => $learningCategory->id],
            ['name' => 'Disabilities', 'category_id' => $learningCategory->id],
            ['name' => 'Deaf community', 'category_id' => $learningCategory->id],
            ['name' => 'Mental health', 'category_id' => $learningCategory->id],
            ['name' => 'Self‑care', 'category_id' => $learningCategory->id],
            ['name' => 'Journaling', 'category_id' => $learningCategory->id],
            ['name' => 'Volunteering', 'category_id' => $learningCategory->id],
            ['name' => 'Community', 'category_id' => $learningCategory->id],
            ['name' => 'Culture', 'category_id' => $learningCategory->id],
            
            // Sports & Recreation
            ['name' => 'Sports', 'category_id' => $sportsCategory->id],
        ];
        
        // Add interests (only if they don't already exist)
        foreach ($newInterests as $interest) {
            Interest::firstOrCreate(
                ['name' => $interest['name']],
                ['category_id' => $interest['category_id']]
            );
        }
        
        $this->command->info('New interests added successfully!');
    }
}

