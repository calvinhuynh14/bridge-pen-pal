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
        // Create interests
        $interests = [
            'Soccer',
            'Football',
            'Basketball',
            'Tennis',
            'Swimming',
            'Running',
            'Cycling',
            'Golf',
            'Baseball',
            'Hockey',
            'Knitting',
            'Crocheting',
            'Painting',
            'Drawing',
            'Photography',
            'Pottery',
            'Sewing',
            'Woodworking',
            'Scrapbooking',
            'Reading',
            'Writing',
            'Gardening',
            'Cooking',
            'Baking',
            'Music',
            'Dancing',
            'Chess',
            'Puzzles',
            'Collecting',
            'Philosophy',
            'History',
            'Science',
            'Languages',
            'Travel',
            'Museums',
            'Theater',
            'Movies',
            'Documentaries'
        ];
        
        foreach ($interests as $interestName) {
            \App\Models\Interest::updateOrCreate(
                ['name' => $interestName]
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
