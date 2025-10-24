<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create interest categories
        $categories = [
            ['name' => 'Sports & Recreation', 'description' => 'Physical activities and sports'],
            ['name' => 'Arts & Crafts', 'description' => 'Creative and artistic pursuits'],
            ['name' => 'Hobbies & Interests', 'description' => 'Personal hobbies and interests'],
            ['name' => 'Learning & Culture', 'description' => 'Educational and cultural activities']
        ];
        
        foreach ($categories as $category) {
            \App\Models\InterestCategory::create($category);
        }
        
        // Create interests
        $interests = [
            // Sports & Recreation (category_id = 1)
            ['name' => 'Soccer', 'category_id' => 1],
            ['name' => 'Football', 'category_id' => 1],
            ['name' => 'Basketball', 'category_id' => 1],
            ['name' => 'Tennis', 'category_id' => 1],
            ['name' => 'Swimming', 'category_id' => 1],
            ['name' => 'Running', 'category_id' => 1],
            ['name' => 'Cycling', 'category_id' => 1],
            ['name' => 'Golf', 'category_id' => 1],
            ['name' => 'Baseball', 'category_id' => 1],
            ['name' => 'Hockey', 'category_id' => 1],
            
            // Arts & Crafts (category_id = 2)
            ['name' => 'Knitting', 'category_id' => 2],
            ['name' => 'Crocheting', 'category_id' => 2],
            ['name' => 'Painting', 'category_id' => 2],
            ['name' => 'Drawing', 'category_id' => 2],
            ['name' => 'Photography', 'category_id' => 2],
            ['name' => 'Pottery', 'category_id' => 2],
            ['name' => 'Sewing', 'category_id' => 2],
            ['name' => 'Woodworking', 'category_id' => 2],
            ['name' => 'Scrapbooking', 'category_id' => 2],
            
            // Hobbies & Interests (category_id = 3)
            ['name' => 'Reading', 'category_id' => 3],
            ['name' => 'Writing', 'category_id' => 3],
            ['name' => 'Gardening', 'category_id' => 3],
            ['name' => 'Cooking', 'category_id' => 3],
            ['name' => 'Baking', 'category_id' => 3],
            ['name' => 'Music', 'category_id' => 3],
            ['name' => 'Dancing', 'category_id' => 3],
            ['name' => 'Chess', 'category_id' => 3],
            ['name' => 'Puzzles', 'category_id' => 3],
            ['name' => 'Collecting', 'category_id' => 3],
            
            // Learning & Culture (category_id = 4)
            ['name' => 'Philosophy', 'category_id' => 4],
            ['name' => 'History', 'category_id' => 4],
            ['name' => 'Science', 'category_id' => 4],
            ['name' => 'Languages', 'category_id' => 4],
            ['name' => 'Travel', 'category_id' => 4],
            ['name' => 'Museums', 'category_id' => 4],
            ['name' => 'Theater', 'category_id' => 4],
            ['name' => 'Movies', 'category_id' => 4],
            ['name' => 'Documentaries', 'category_id' => 4]
        ];
        
        foreach ($interests as $interest) {
            \App\Models\Interest::create($interest);
        }
        
        // Create languages
        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Chinese', 'code' => 'zh'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'Russian', 'code' => 'ru']
        ];
        
        foreach ($languages as $language) {
            \App\Models\Language::create($language);
        }
    }
}
