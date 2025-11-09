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
            \App\Models\InterestCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
        
        // Get category IDs after creation
        $sportsCategory = \App\Models\InterestCategory::where('name', 'Sports & Recreation')->first();
        $artsCategory = \App\Models\InterestCategory::where('name', 'Arts & Crafts')->first();
        $hobbiesCategory = \App\Models\InterestCategory::where('name', 'Hobbies & Interests')->first();
        $learningCategory = \App\Models\InterestCategory::where('name', 'Learning & Culture')->first();
        
        // Create interests
        $interests = [
            // Sports & Recreation
            ['name' => 'Soccer', 'category_id' => $sportsCategory->id],
            ['name' => 'Football', 'category_id' => $sportsCategory->id],
            ['name' => 'Basketball', 'category_id' => $sportsCategory->id],
            ['name' => 'Tennis', 'category_id' => $sportsCategory->id],
            ['name' => 'Swimming', 'category_id' => $sportsCategory->id],
            ['name' => 'Running', 'category_id' => $sportsCategory->id],
            ['name' => 'Cycling', 'category_id' => $sportsCategory->id],
            ['name' => 'Golf', 'category_id' => $sportsCategory->id],
            ['name' => 'Baseball', 'category_id' => $sportsCategory->id],
            ['name' => 'Hockey', 'category_id' => $sportsCategory->id],
            
            // Arts & Crafts
            ['name' => 'Knitting', 'category_id' => $artsCategory->id],
            ['name' => 'Crocheting', 'category_id' => $artsCategory->id],
            ['name' => 'Painting', 'category_id' => $artsCategory->id],
            ['name' => 'Drawing', 'category_id' => $artsCategory->id],
            ['name' => 'Photography', 'category_id' => $artsCategory->id],
            ['name' => 'Pottery', 'category_id' => $artsCategory->id],
            ['name' => 'Sewing', 'category_id' => $artsCategory->id],
            ['name' => 'Woodworking', 'category_id' => $artsCategory->id],
            ['name' => 'Scrapbooking', 'category_id' => $artsCategory->id],
            
            // Hobbies & Interests
            ['name' => 'Reading', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Writing', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Gardening', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Cooking', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Baking', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Music', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Dancing', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Chess', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Puzzles', 'category_id' => $hobbiesCategory->id],
            ['name' => 'Collecting', 'category_id' => $hobbiesCategory->id],
            
            // Learning & Culture
            ['name' => 'Philosophy', 'category_id' => $learningCategory->id],
            ['name' => 'History', 'category_id' => $learningCategory->id],
            ['name' => 'Science', 'category_id' => $learningCategory->id],
            ['name' => 'Languages', 'category_id' => $learningCategory->id],
            ['name' => 'Travel', 'category_id' => $learningCategory->id],
            ['name' => 'Museums', 'category_id' => $learningCategory->id],
            ['name' => 'Theater', 'category_id' => $learningCategory->id],
            ['name' => 'Movies', 'category_id' => $learningCategory->id],
            ['name' => 'Documentaries', 'category_id' => $learningCategory->id]
        ];
        
        foreach ($interests as $interest) {
            \App\Models\Interest::updateOrCreate(
                ['name' => $interest['name'], 'category_id' => $interest['category_id']],
                $interest
            );
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
            \App\Models\Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
