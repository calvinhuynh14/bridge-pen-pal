<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test organizations
        $organizations = [
            ['name' => 'Sunshine Senior Care'],
            ['name' => 'Golden Years Foundation'],
            ['name' => 'Elderly Care Network'],
            ['name' => 'Community Senior Services'],
            ['name' => 'Aging Gracefully Center'],
        ];

        $organizationIds = [];
        foreach ($organizations as $org) {
            $existing = DB::table('organization')->where('name', $org['name'])->first();
            if ($existing) {
                $orgId = $existing->id;
            } else {
            $orgId = DB::table('organization')->insertGetId([
                'name' => $org['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }
            $organizationIds[] = $orgId;
        }

        // Get user type IDs
        $adminTypeId = DB::table('user_types')->where('name', 'admin')->value('id');
        $volunteerTypeId = DB::table('user_types')->where('name', 'volunteer')->value('id');
        $residentTypeId = DB::table('user_types')->where('name', 'resident')->value('id');

        // Create admin users for each organization
        $adminUsers = [];
        foreach ($organizationIds as $index => $orgId) {
            $email = 'admin' . ($index + 1) . '@test.com';
            
            // Check if admin user already exists
            $existingUser = DB::table('users')->where('email', $email)->first();
            if ($existingUser) {
                $adminId = $existingUser->id;
            } else {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin ' . ($index + 1),
                    'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }

            // Check if admin record already exists
            $existingAdmin = DB::table('admin')->where('user_id', $adminId)->first();
            if (!$existingAdmin) {
            DB::table('admin')->insert([
                'user_id' => $adminId,
                'organization_id' => $orgId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }

            $adminUsers[] = $adminId;
        }

        // Create volunteer applicants
        $volunteerNames = [
            'Alice Johnson', 'Bob Smith', 'Carol Davis', 'David Wilson', 'Emma Brown',
            'Frank Miller', 'Grace Lee', 'Henry Taylor', 'Ivy Chen', 'Jack Anderson',
            'Kate Martinez', 'Liam O\'Connor', 'Maya Patel', 'Noah Kim', 'Olivia White',
            'Paul Rodriguez', 'Quinn Thompson', 'Rachel Green', 'Sam Johnson', 'Tina Wang',
            'Uma Singh', 'Victor Lopez', 'Wendy Chen', 'Xavier Martinez', 'Yara Ahmed',
            'Zoe Williams', 'Aaron Kim', 'Bella Rodriguez', 'Carlos Silva', 'Diana Park',
            'Ethan Murphy', 'Fiona O\'Brien', 'George Zhang', 'Hannah Kim', 'Ian Foster',
            'Jasmine Lee', 'Kevin Park', 'Luna Rodriguez', 'Marcus Johnson', 'Nina Patel',
            'Oscar Chen', 'Penelope White', 'Quincy Davis', 'Ruby Singh', 'Sebastian Kim',
            'Tara Williams', 'Ulysses Martinez', 'Vera Johnson', 'Winston Lee', 'Xara Smith'
        ];

        $volunteerEmails = [
            'alice.johnson@email.com', 'bob.smith@email.com', 'carol.davis@email.com',
            'david.wilson@email.com', 'emma.brown@email.com', 'frank.miller@email.com',
            'grace.lee@email.com', 'henry.taylor@email.com', 'ivy.chen@email.com',
            'jack.anderson@email.com', 'kate.martinez@email.com', 'liam.oconnor@email.com',
            'maya.patel@email.com', 'noah.kim@email.com', 'olivia.white@email.com',
            'paul.rodriguez@email.com', 'quinn.thompson@email.com', 'rachel.green@email.com',
            'sam.johnson@email.com', 'tina.wang@email.com', 'uma.singh@email.com',
            'victor.lopez@email.com', 'wendy.chen@email.com', 'xavier.martinez@email.com',
            'yara.ahmed@email.com', 'zoe.williams@email.com', 'aaron.kim@email.com',
            'bella.rodriguez@email.com', 'carlos.silva@email.com', 'diana.park@email.com',
            'ethan.murphy@email.com', 'fiona.obrien@email.com', 'george.zhang@email.com',
            'hannah.kim@email.com', 'ian.foster@email.com', 'jasmine.lee@email.com',
            'kevin.park@email.com', 'luna.rodriguez@email.com', 'marcus.johnson@email.com',
            'nina.patel@email.com', 'oscar.chen@email.com', 'penelope.white@email.com',
            'quincy.davis@email.com', 'ruby.singh@email.com', 'sebastian.kim@email.com',
            'tara.williams@email.com', 'ulysses.martinez@email.com', 'vera.johnson@email.com',
            'winston.lee@email.com', 'xara.smith@email.com'
        ];

        $applicationMessages = [
            'I have always wanted to help seniors and make a difference in their lives. I have experience volunteering at nursing homes.',
            'I am passionate about connecting with elderly individuals and providing companionship. I believe everyone deserves meaningful relationships.',
            'I have a background in healthcare and would love to use my skills to help seniors through pen pal correspondence.',
            'I am a retired teacher and would love to share my love of learning with senior residents through letter writing.',
            'I have been looking for meaningful volunteer work and this pen pal program seems perfect for me.',
            'I have experience with elderly care and would love to participate in this program to provide companionship.',
            'I am a college student studying social work and this opportunity aligns perfectly with my career goals.',
            'I have always had a special connection with seniors and would love to be part of this program.',
            'I am looking for ways to give back to the community and this pen pal program seems like a great opportunity.',
            'I have experience writing letters and would love to use this skill to connect with senior residents.',
            'I am passionate about intergenerational connections and believe this program will be very rewarding.',
            'I have worked with seniors before and would love to continue this meaningful work through pen pal correspondence.',
            'I am a writer and would love to use my skills to bring joy to senior residents through letter writing.',
            'I have always been close to my grandparents and would love to extend that care to other seniors.',
            'I am looking for volunteer opportunities that allow me to make a real difference in people\'s lives.',
            'I have experience in elderly care and would love to participate in this meaningful program.',
            'I am passionate about helping others and believe this pen pal program will be very fulfilling.',
            'I have always wanted to volunteer with seniors and this program seems like the perfect opportunity.',
            'I am a retired professional and would love to use my experience to help senior residents.',
            'I have a heart for service and would love to be part of this wonderful program.',
            'I am looking for meaningful volunteer work and this pen pal program seems perfect for me.',
            'I have experience with elderly care and would love to participate in this program.',
            'I am passionate about connecting with seniors and providing companionship.',
            'I have always wanted to help seniors and make a difference in their lives.',
            'I am a college student and would love to gain experience working with seniors.',
            'I have a background in healthcare and would love to use my skills to help seniors.',
            'I am looking for ways to give back to the community and this program seems perfect.',
            'I have experience writing letters and would love to use this skill to connect with seniors.',
            'I am passionate about intergenerational connections and believe this will be rewarding.',
            'I have worked with seniors before and would love to continue this meaningful work.',
            'I am a writer and would love to use my skills to bring joy to senior residents.',
            'I have always been close to my grandparents and would love to extend that care.',
            'I am looking for volunteer opportunities that allow me to make a real difference.',
            'I have experience in elderly care and would love to participate in this program.',
            'I am passionate about helping others and believe this program will be fulfilling.',
            'I have always wanted to volunteer with seniors and this seems perfect.',
            'I am a retired professional and would love to use my experience to help seniors.',
            'I have a heart for service and would love to be part of this wonderful program.',
            'I am looking for meaningful volunteer work and this program seems perfect.',
            'I have experience with elderly care and would love to participate.',
            'I am passionate about connecting with seniors and providing companionship.',
            'I have always wanted to help seniors and make a difference.',
            'I am a college student and would love to gain experience with seniors.',
            'I have a background in healthcare and would love to use my skills.',
            'I am looking for ways to give back and this program seems perfect.',
            'I have experience writing letters and would love to use this skill.',
            'I am passionate about intergenerational connections and believe this will be rewarding.',
            'I have worked with seniors before and would love to continue.',
            'I am a writer and would love to use my skills to bring joy.',
            'I have always been close to my grandparents and would love to extend.'
        ];

        $statuses = ['pending', 'approved', 'rejected'];
        $statusWeights = [60, 30, 10]; // 60% pending, 30% approved, 10% rejected

        // Create volunteer users and applications
        for ($i = 0; $i < 50; $i++) {
            $email = $volunteerEmails[$i];
            
            // Check if user already exists
            $existingUser = DB::table('users')->where('email', $email)->first();
            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $volunteerNames[$i],
                    'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'user_type_id' => $volunteerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }

            // Assign to random organization
            $orgId = $organizationIds[array_rand($organizationIds)];
            
            // Check if volunteer application already exists
            $existingVolunteer = DB::table('volunteer')->where('user_id', $userId)->first();
            if (!$existingVolunteer) {
            // Determine status based on weights
            $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
            
            // Create volunteer application
            DB::table('volunteer')->insert([
                'user_id' => $userId,
                'organization_id' => $orgId,
                'status' => $status,
                'application_date' => now()->subDays(rand(1, 90)), // Random date within last 90 days
                    'application_notes' => $applicationMessages[$i % count($applicationMessages)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }
        }

        // Create 30 residents with proper usernames and PINs
        $residentNames = [
            'Alice Johnson', 'Bob Smith', 'Carol Davis', 'David Wilson', 'Emma Brown',
            'Frank Miller', 'Grace Lee', 'Henry Taylor', 'Ivy Anderson', 'Jack Thomas',
            'Karen White', 'Liam Harris', 'Mia Martin', 'Noah Thompson', 'Olivia Garcia',
            'Peter Martinez', 'Quinn Robinson', 'Rachel Clark', 'Sam Rodriguez', 'Tina Lewis',
            'Uma Walker', 'Victor Hall', 'Wendy Allen', 'Xavier Young', 'Yara King',
            'Zoe Wright', 'Adam Lopez', 'Beth Hill', 'Carl Scott', 'Diana Green'
        ];

        $roomNumbers = ['101', '102', '103', '104', '105', '201', '202', '203', '204', '205', 
                       '301', '302', '303', '304', '305', '401', '402', '403', '404', '405'];
        $floorNumbers = ['1', '1', '1', '1', '1', '2', '2', '2', '2', '2', 
                        '3', '3', '3', '3', '3', '4', '4', '4', '4', '4'];
        $birthYears = [1930, 1935, 1940, 1945, 1950, 1955, 1960, 1965, 1970, 1975];

        for ($i = 0; $i < 30; $i++) {
            // Generate 6-digit resident ID starting from 100000
            $residentId = 100000 + $i;
            $username = (string)$residentId;
            
            // Check if resident user already exists
            $existingUser = DB::table('users')->where('username', $username)->first();
            if ($existingUser) {
                $user_id = $existingUser->id;
            } else {
            $orgId = $organizationIds[array_rand($organizationIds)]; // Random organization
                $status = ['pending', 'approved'][array_rand(['pending', 'approved'])]; // Mostly approved residents
            $applicationDate = now()->subDays(rand(1, 180)); // Random date in last 6 months
                
                // Generate 6-digit PIN
                $pinCode = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                
                // Generate birth date
                $birthYear = $birthYears[array_rand($birthYears)];
                $birthMonth = rand(1, 12);
                $birthDay = rand(1, 28); // Safe day for all months
                $dateOfBirth = $birthYear . '-' . str_pad($birthMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($birthDay, 2, '0', STR_PAD_LEFT);
                
                // Get room and floor
                $roomNumber = $roomNumbers[$i % count($roomNumbers)];
                $floorNumber = $floorNumbers[$i % count($floorNumbers)];

            $user_id = DB::table('users')->insertGetId([
                'name' => $residentNames[$i],
                    'email' => null, // Residents don't have email
                    'username' => $username, // 6-digit username
                    'password' => Hash::make($pinCode), // PIN as password
                'user_type_id' => $residentTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('resident')->insert([
                'user_id' => $user_id,
                'organization_id' => $orgId,
                'status' => $status,
                'application_date' => $applicationDate,
                    'date_of_birth' => $dateOfBirth,
                    'room_number' => $roomNumber,
                    'floor_number' => $floorNumber,
                    'pin_code' => $pinCode, // Store plain PIN for admin viewing
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            }
        }
        $this->command->info('Created 30 Residents.');

        // Assign interests and languages to users
        $this->assignInterestsAndLanguages();
        $this->command->info('Assigned interests and languages to users.');

        // Create featured stories for organizations
        $this->seedFeaturedStories($organizationIds);
        $this->command->info('Created featured stories for organizations.');

        // Create mock letters (only if none exist)
        $existingLetters = DB::table('letters')->count();
        if ($existingLetters === 0) {
            $this->seedLetters($organizationIds);
            $this->command->info('Created mock letters.');
        } else {
            $this->command->info("Skipping letter seeding - {$existingLetters} letters already exist.");
        }

        // Always create/refresh in-transit letters to demonstrate 8-hour delivery delay
        // Delete existing in-transit letters first to avoid duplicates
        $now = now();
        DB::table('letters')
            ->where('delivered_at', '>', $now)
            ->where('is_open_letter', false)
            ->delete();
        
        // Get residents and volunteers for in-transit letters
        $residents = DB::select("
            SELECT u.id, u.name, r.organization_id
            FROM users u
            JOIN resident r ON u.id = r.user_id
            LIMIT 10
        ");
        
        $volunteers = DB::select("
            SELECT u.id, u.name, v.organization_id
            FROM users u
            JOIN volunteer v ON u.id = v.user_id
            WHERE v.status = 'approved'
            LIMIT 10
        ");
        
        if (!empty($residents) && !empty($volunteers)) {
            $this->createInTransitLetters($residents, $volunteers, $now);
        }

        // Seed correspondence for resident 100000 (for testing Write page)
        $this->seedCorrespondenceForResident100000();
        $this->command->info('Created correspondence data for resident 100000.');

        // Create unread letters between corresponding users
        $this->seedUnreadLetters();
        $this->command->info('Created unread letters for testing.');

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 5 Organizations');
        $this->command->info('- 5 Admin Users');
        $this->command->info('- 50 Volunteer Applications');
        $this->command->info('- 30 Residents');
        $this->command->info('- Interest Categories & Interests');
        $this->command->info('- Languages');
        $this->command->info('- Mock Letters (open letters, sent letters, etc.)');
        $this->command->info('');
        $this->command->info('Demo Login Credentials:');
        $this->command->info('Admin Users:');
        for ($i = 1; $i <= 5; $i++) {
            $this->command->info("  Admin {$i}: admin{$i}@test.com / password");
        }
        $this->command->info('');
        $this->command->info('Resident Users (Username + PIN):');
        for ($i = 0; $i < 5; $i++) {
            $residentId = 100000 + $i;
            $resident = DB::table('resident')
                ->join('users', 'resident.user_id', '=', 'users.id')
                ->where('users.username', $residentId)
                ->select('users.name', 'resident.pin_code')
                ->first();
            if ($resident) {
                $this->command->info("  {$resident->name}: {$residentId} / {$resident->pin_code}");
            }
        }
    }

    /**
     * Seed mock letters
     */
    private function seedLetters($organizationIds)
    {
        // Get approved residents (for open letters)
        $residents = DB::select("
            SELECT u.id, u.name, r.organization_id
            FROM users u
            JOIN resident r ON u.id = r.user_id
            WHERE r.status = 'approved'
            LIMIT 10
        ");

        // Get approved volunteers (for sent letters)
        $volunteers = DB::select("
            SELECT u.id, u.name, v.organization_id
            FROM users u
            JOIN volunteer v ON u.id = v.user_id
            WHERE v.status = 'approved'
            LIMIT 5
        ");

        if (empty($residents)) {
            $this->command->warn('No approved residents found. Skipping letter seeding.');
            return;
        }

        $letterContents = [
            // Open letters from residents
            "Hello! I'm looking for someone to write to. I love gardening and reading books. Would anyone like to be my pen pal?",
            "Hi there! I'm a resident here and I'd love to connect with someone through letters. I enjoy talking about history and sharing stories.",
            "Greetings! I'm hoping to find a pen pal who enjoys conversation. I like discussing current events and learning about different places.",
            "Hello! I'm interested in making new friends through letter writing. I love cooking and would enjoy sharing recipes!",
            "Hi! I'm looking for someone to correspond with. I enjoy reading mystery novels and watching classic movies.",
            "Greetings! I'd love to connect with someone through letters. I'm interested in art, music, and hearing about your experiences.",
            "Hello! I'm seeking a pen pal for meaningful conversations. I enjoy discussing books, movies, and life experiences.",
            "Hi there! I'm a resident who loves writing and receiving letters. I'd be happy to share stories and learn about you!",
            "Greetings! I'm looking for someone to write to regularly. I enjoy talking about family, hobbies, and daily life.",
            "Hello! I'm interested in finding a pen pal. I love sharing memories and hearing about other people's experiences.",
            
            // Regular letters
            "Thank you for your last letter! I really enjoyed reading about your trip. I'd love to hear more about it.",
            "I hope this letter finds you well. I wanted to share some exciting news with you!",
            "It's been wonderful corresponding with you. I wanted to tell you about something that happened recently.",
            "I'm writing to thank you for your thoughtful letter. Your words really brightened my day!",
            "Hello! I wanted to reach out and see how you're doing. I've been thinking about our conversation.",
        ];

        $now = now();

        // Create 15-20 open letters from residents (for Discover page testing)
        // Mix of residents with common interests and those without
        $openLetterCount = min(20, count($residents));
        for ($i = 0; $i < $openLetterCount; $i++) {
            // Select residents strategically: first few have common interests, others are random
            if ($i < min(10, count($residents))) {
                // Use residents with assigned interests (from assignInterestsAndLanguages)
                $resident = $residents[$i % count($residents)];
            } else {
                // Random residents
                $resident = $residents[array_rand($residents)];
            }
            
            $content = $letterContents[$i % count($letterContents)];
            
            // Random sent date within last 30 days
            $sentAt = $now->copy()->subDays(rand(0, 30))->subHours(rand(0, 23));
            $deliveredAt = $sentAt->copy()->addHours(8); // 8-hour delay
            
            // Determine status based on delivery time
            $status = $deliveredAt->isPast() ? 'delivered' : 'sent';

            DB::table('letters')->insert([
                'sender_id' => $resident->id,
                'receiver_id' => null,
                'content' => $content,
                'is_open_letter' => true,
                'parent_letter_id' => null,
                'status' => $status,
                'sent_at' => $sentAt,
                'delivered_at' => $deliveredAt,
                'read_at' => null,
                'created_at' => $sentAt,
                'updated_at' => $now,
            ]);
        }

        // Create 5-7 regular sent letters (between residents and volunteers)
        if (!empty($volunteers)) {
            $regularLetterCount = min(7, count($residents));
            for ($i = 0; $i < $regularLetterCount; $i++) {
                // Randomly pair a resident with a volunteer
                $sender = $residents[array_rand($residents)];
                $receiver = $volunteers[array_rand($volunteers)];
                
                // Sometimes reverse the sender/receiver
                if (rand(0, 1)) {
                    $temp = $sender;
                    $sender = $receiver;
                    $receiver = $temp;
                }
                
                $content = $letterContents[10 + ($i % 5)]; // Use regular letter contents
                
                // Random sent date within last 14 days
                $sentAt = $now->copy()->subDays(rand(0, 14))->subHours(rand(0, 23));
                $deliveredAt = $sentAt->copy()->addHours(8);
                
                // Determine status
                $status = 'sent';
                $readAt = null;
                if ($deliveredAt->isPast()) {
                    $status = 'delivered';
                    // Sometimes mark as read
                    if (rand(0, 1)) {
                        $readAt = $deliveredAt->copy()->addHours(rand(1, 48));
                        $status = 'read';
                    }
                }

                DB::table('letters')->insert([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'content' => $content,
                    'is_open_letter' => false,
                    'parent_letter_id' => null,
                    'status' => $status,
                    'sent_at' => $sentAt,
                    'delivered_at' => $deliveredAt,
                    'read_at' => $readAt,
                    'created_at' => $sentAt,
                    'updated_at' => $now,
                ]);
            }
        }

        // Create 2-3 letters between residents
        $residentLetterCount = min(3, count($residents) - 1);
        for ($i = 0; $i < $residentLetterCount; $i++) {
            $residentPair = array_rand($residents, 2);
            $sender = $residents[$residentPair[0]];
            $receiver = $residents[$residentPair[1]];
            
            $content = $letterContents[10 + ($i % 5)];
            
            $sentAt = $now->copy()->subDays(rand(0, 7))->subHours(rand(0, 23));
            $deliveredAt = $sentAt->copy()->addHours(8);
            
            $status = $deliveredAt->isPast() ? 'delivered' : 'sent';
            $readAt = null;
            if ($status === 'delivered' && rand(0, 1)) {
                $readAt = $deliveredAt->copy()->addHours(rand(1, 24));
                $status = 'read';
            }

            DB::table('letters')->insert([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => $content,
                'is_open_letter' => false,
                'parent_letter_id' => null,
                'status' => $status,
                'sent_at' => $sentAt,
                'delivered_at' => $deliveredAt,
                'read_at' => $readAt,
                'created_at' => $sentAt,
                'updated_at' => $now,
            ]);
        }

        // Create letters currently in transit to demonstrate 8-hour delivery delay
        $this->createInTransitLetters($residents, $volunteers, $now);
    }

    /**
     * Create letters that are currently in transit (sent recently, not yet delivered)
     * This demonstrates the 8-hour delivery delay feature
     */
    private function createInTransitLetters($residents, $volunteers, $now)
    {
        if (empty($residents) || empty($volunteers)) {
            return;
        }

        $inTransitContents = [
            "I'm so excited to write to you! I've been thinking about what to say all day.",
            "Thank you for your last letter. It really made my day brighter!",
            "I wanted to share something special with you today.",
            "Your words always bring a smile to my face. Thank you for being my pen pal!",
            "I hope this letter finds you well. I've been doing great lately!",
            "I have some wonderful news to share with you in this letter.",
            "Your friendship means so much to me. Thank you for writing!",
            "I've been thinking about what you wrote in your last letter.",
        ];

        // Create 8-12 letters in transit with varying delivery times
        $inTransitCount = min(12, count($residents), count($volunteers));
        
        for ($i = 0; $i < $inTransitCount; $i++) {
            // Alternate between volunteer->resident and resident->volunteer
            if ($i % 2 === 0) {
                $sender = $volunteers[array_rand($volunteers)];
                $receiver = $residents[array_rand($residents)];
            } else {
                $sender = $residents[array_rand($residents)];
                $receiver = $volunteers[array_rand($volunteers)];
            }

            // Ensure sender and receiver are different
            if ($sender->id === $receiver->id) {
                continue;
            }

            $content = $inTransitContents[$i % count($inTransitContents)];

            // Create letters with different delivery times:
            // - Some sent 1-2 hours ago (arriving in 6-7 hours)
            // - Some sent 3-4 hours ago (arriving in 4-5 hours)
            // - Some sent 5-6 hours ago (arriving in 2-3 hours)
            // - Some sent 7 hours ago (arriving in ~1 hour)
            $mod = $i % 4;
            if ($mod === 0) {
                $hoursAgo = rand(1, 2);      // Arriving in 6-7 hours
            } elseif ($mod === 1) {
                $hoursAgo = rand(3, 4);      // Arriving in 4-5 hours
            } elseif ($mod === 2) {
                $hoursAgo = rand(5, 6);      // Arriving in 2-3 hours
            } else {
                $hoursAgo = 7;               // Arriving in ~1 hour
            }

            $sentAt = $now->copy()->subHours($hoursAgo);
            $deliveredAt = $sentAt->copy()->addHours(8); // 8-hour delay

            // Status should be 'sent' since it hasn't arrived yet
            $status = 'sent';

            DB::table('letters')->insert([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => $content,
                'is_open_letter' => false,
                'parent_letter_id' => null,
                'status' => $status,
                'sent_at' => $sentAt,
                'delivered_at' => $deliveredAt,
                'read_at' => null,
                'created_at' => $sentAt,
                'updated_at' => $now,
            ]);
        }

        $this->command->info("Created {$inTransitCount} letters currently in transit (demonstrating 8-hour delivery delay)");
    }

    /**
     * Seed correspondence for resident 100000 (for testing Write page)
     * Creates multiple pen pals and 25+ messages to test pagination
     */
    private function seedCorrespondenceForResident100000()
    {
        // Get resident 100000
        $resident100000 = DB::table('users')
            ->where('username', '100000')
            ->first();

        if (!$resident100000) {
            $this->command->warn('Resident 100000 not found. Skipping correspondence seeding.');
            return;
        }

        // Get other approved residents to be pen pals (exclude resident 100000)
        $penPals = DB::select("
            SELECT u.id, u.name
            FROM users u
            JOIN resident r ON u.id = r.user_id
            WHERE r.status = 'approved'
            AND u.id != ?
            LIMIT 3
        ", [$resident100000->id]);

        if (empty($penPals)) {
            $this->command->warn('No other residents found for pen pals. Skipping correspondence seeding.');
            return;
        }

        $now = now();
        $correspondenceMessages = [
            // Conversation starter messages
            "Hello! I hope this letter finds you well. I wanted to reach out and share some thoughts with you.",
            "Thank you for your letter! I really enjoyed reading it and would love to continue our conversation.",
            "I'm so glad you responded! I've been thinking about what you said and I completely agree. Life has been quite interesting lately, and I'd love to hear more about your experiences.",
            "That's wonderful to hear! I've been keeping busy with various projects. The weather has been lovely this week, perfect for taking walks in the park. How have you been spending your days?",
            "I've been reading a fascinating book lately. It's about the history of our city and I'm learning so much. Have you read anything interesting recently?",
            "That sounds intriguing! I haven't read much lately, but I've been meaning to pick up a new book. What's the title? I might check it out from the library.",
            "It's called 'The Story of Our Town' by Margaret Johnson. I think you'd really enjoy it. The author has such a beautiful way of describing the places we know so well.",
            "Thank you for the recommendation! I'll definitely look for it. By the way, I tried that recipe you mentioned in your last letter and it turned out great. My family loved it!",
            "I'm so happy to hear that! Cooking is one of my favorite hobbies. I'm always experimenting with new recipes. Do you enjoy cooking as well?",
            "I do enjoy it, though I'm still learning. I'd love to exchange more recipes with you. Maybe we could share our favorites?",
            "That's a wonderful idea! I'll send you one of my grandmother's recipes in my next letter. She was an amazing cook and I think you'll love her apple pie recipe.",
            "I can't wait to try it! My grandmother also had some amazing recipes. I'll share one of hers with you too.",
            "How wonderful! I love hearing about family traditions. Food really brings people together, doesn't it?",
            "Absolutely! Some of my best memories are from family gatherings around the dinner table. What are some of your favorite family memories?",
            "I have so many wonderful memories. One that stands out is when my whole family would gather for holidays. The house would be full of laughter and love.",
            "That sounds beautiful. Family is so important. I'm grateful for the connections I've made here and through letters like this.",
            "Me too! Writing letters has been such a meaningful way to connect with others. I'm glad we started this correspondence.",
            "I completely agree. There's something special about taking the time to write and receive letters. It feels more personal than other forms of communication.",
            "Yes, exactly! I find myself thinking more carefully about what I want to say when I'm writing a letter. It's a nice change of pace.",
            "I feel the same way. It's given me a chance to reflect on my thoughts and share them in a meaningful way.",
            "I've really enjoyed our conversations. I hope we can continue writing to each other for a long time.",
            "I hope so too! It's been wonderful getting to know you through these letters.",
            "Thank you for being such a thoughtful pen pal. Your letters always brighten my day.",
            "You're very kind to say that. Your letters do the same for me!",
            "I'm looking forward to your next letter. Until then, take care!",
            "Take care as well! I'll write again soon.",
            "I wanted to share something exciting that happened yesterday. I finally finished that puzzle I've been working on for weeks!",
            "Congratulations! I know how satisfying it is to complete something you've been working on. What was the picture of?",
            "It was a beautiful landscape of the mountains. It reminded me of a trip I took years ago with my family.",
            "That sounds like a wonderful memory. I've always wanted to visit the mountains. Maybe one day I'll get the chance.",
            "I hope you do! The views are absolutely breathtaking. I'll send you a postcard if I ever go back.",
            "I'd love that! Speaking of travel, have you ever been to the coast? I've always been drawn to the ocean.",
            "Yes, I have! There's something so peaceful about watching the waves. I find it very calming.",
            "I completely understand. Nature has a way of bringing peace to our lives, doesn't it?",
            "It really does. I try to spend time outside every day, even if it's just sitting in the garden.",
            "That's a lovely habit. I should try to do the same. What kind of flowers do you have in your garden?",
            "I have roses, daisies, and some herbs. I love watching them grow and change with the seasons.",
            "How wonderful! Gardening must be very rewarding. I've always wanted to try growing my own vegetables.",
            "You should! There's nothing quite like eating something you've grown yourself. I'd be happy to share some tips if you're interested.",
            "I would love that! Thank you for offering. Maybe we could exchange gardening stories in our letters.",
            "That sounds like a great idea! I'm always happy to talk about my garden and learn from others too.",
        ];

        // Find Bob Smith specifically
        $bobSmith = DB::table('users')
            ->where('name', 'Bob Smith')
            ->first();

        // Create correspondence with each pen pal
        foreach ($penPals as $penPal) {
            // Bob Smith gets 30 messages, others get 8-10
            $messageCount = ($bobSmith && $penPal->id == $bobSmith->id) ? 30 : rand(8, 10);
            
            for ($i = 0; $i < $messageCount; $i++) {
                // Alternate sender between resident 100000 and pen pal
                $senderId = ($i % 2 === 0) ? $resident100000->id : $penPal->id;
                $receiverId = ($i % 2 === 0) ? $penPal->id : $resident100000->id;
                
                // Use different messages for variety
                $content = $correspondenceMessages[$i % count($correspondenceMessages)];
                
                // Create messages over the past 90 days for Bob Smith (to fit 30 messages), 30 days for others
                $maxDays = ($bobSmith && $penPal->id == $bobSmith->id) ? 90 : 30;
                $daysAgo = ($i * $maxDays) / $messageCount; // Space messages evenly
                $sentAt = $now->copy()->subDays($daysAgo)->subHours(rand(0, 23));
                $deliveredAt = $sentAt->copy()->addHours(8);
                
                // Determine status
                $status = 'delivered';
                $readAt = null;
                if ($deliveredAt->isPast()) {
                    // Sometimes mark as read
                    if (rand(0, 2) > 0) { // 66% chance of being read
                        $readAt = $deliveredAt->copy()->addHours(rand(1, 48));
                        $status = 'read';
                    }
                } else {
                    $status = 'sent';
                }

                DB::table('letters')->insert([
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'content' => $content,
                    'is_open_letter' => false,
                    'parent_letter_id' => null,
                    'status' => $status,
                    'sent_at' => $sentAt,
                    'delivered_at' => $deliveredAt,
                    'read_at' => $readAt,
                    'created_at' => $sentAt,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info("Created correspondence for resident 100000 with " . count($penPals) . " pen pal(s).");
    }

    /**
     * Assign interests and languages to users for testing discover page matching
     */
    private function assignInterestsAndLanguages()
    {
        // Get all interests and languages
        $allInterests = DB::table('interests')->pluck('id', 'name')->toArray();
        $allLanguages = DB::table('languages')->pluck('id', 'name')->toArray();

        // Get approved volunteers and residents
        $volunteers = DB::select("
            SELECT u.id, u.name, u.email
            FROM users u
            JOIN volunteer v ON u.id = v.user_id
            WHERE v.status = 'approved'
        ");

        $residents = DB::select("
            SELECT u.id, u.name
            FROM users u
            JOIN resident r ON u.id = r.user_id
            WHERE r.status = 'approved'
        ");

        // Define interest groups for testing common interests
        $interestGroups = [
            ['Reading', 'Books', 'Writing', 'Poetry'],
            ['Art', 'Painting', 'Drawing', 'Photography'],
            ['Music', 'Singing', 'Dancing'],
            ['Cooking', 'Baking', 'Food'],
            ['Gardening', 'Nature', 'Walking'],
            ['Sports', 'Fitness', 'Yoga'],
            ['Gaming', 'Video games', 'Board games'],
            ['Movies', 'TV shows', 'Classic films'],
            ['Travel', 'Culture', 'History'],
            ['Technology', 'Computers', 'Programming'],
        ];

        // Define language groups for testing common languages
        $languageGroups = [
            ['English', 'Spanish'],
            ['English', 'French'],
            ['English', 'German'],
            ['Spanish', 'French'],
            ['English', 'Chinese'],
        ];

        // Assign interests to volunteers (ensure some have overlapping interests)
        foreach ($volunteers as $index => $volunteer) {
            $groupIndex = $index % count($interestGroups);
            $selectedInterests = $interestGroups[$groupIndex];
            
            // Add 1-2 random additional interests
            $availableInterests = array_diff_key($allInterests, array_flip($selectedInterests));
            if (!empty($availableInterests)) {
                $randomKeys = array_rand($availableInterests, min(rand(1, 2), count($availableInterests)));
                if (!is_array($randomKeys)) {
                    $randomKeys = [$randomKeys];
                }
                $randomInterests = array_keys(array_intersect_key($allInterests, array_flip($randomKeys)));
                $selectedInterests = array_merge($selectedInterests, $randomInterests);
            }

            foreach ($selectedInterests as $interestName) {
                if (isset($allInterests[$interestName])) {
                    DB::table('user_interests')->updateOrInsert(
                        ['user_id' => $volunteer->id, 'interest_id' => $allInterests[$interestName]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Assign languages
            $langGroupIndex = $index % count($languageGroups);
            $selectedLanguages = $languageGroups[$langGroupIndex];
            foreach ($selectedLanguages as $langName) {
                if (isset($allLanguages[$langName])) {
                    DB::table('user_languages')->updateOrInsert(
                        ['user_id' => $volunteer->id, 'language_id' => $allLanguages[$langName]],
                        [
                            'proficiency_level' => ['beginner', 'intermediate', 'fluent', 'native'][rand(0, 3)],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }

        // Assign interests to residents (ensure some match volunteers for testing)
        foreach ($residents as $index => $resident) {
            // Some residents share interests with volunteers (for testing matching)
            if ($index < count($interestGroups)) {
                $selectedInterests = $interestGroups[$index];
            } else {
                // Others get random interests
                $randomKeys = array_rand($allInterests, min(3, count($allInterests)));
                if (!is_array($randomKeys)) {
                    $randomKeys = [$randomKeys];
                }
                $selectedInterests = array_keys(array_intersect_key($allInterests, array_flip($randomKeys)));
            }

            foreach ($selectedInterests as $interestName) {
                if (isset($allInterests[$interestName])) {
                    DB::table('user_interests')->updateOrInsert(
                        ['user_id' => $resident->id, 'interest_id' => $allInterests[$interestName]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Assign languages (some match volunteers)
            if ($index < count($languageGroups)) {
                $selectedLanguages = $languageGroups[$index];
            } else {
                $randomKeys = array_rand($allLanguages, min(2, count($allLanguages)));
                if (!is_array($randomKeys)) {
                    $randomKeys = [$randomKeys];
                }
                $selectedLanguages = array_keys(array_intersect_key($allLanguages, array_flip($randomKeys)));
            }

            foreach ($selectedLanguages as $langName) {
                if (isset($allLanguages[$langName])) {
                    DB::table('user_languages')->updateOrInsert(
                        ['user_id' => $resident->id, 'language_id' => $allLanguages[$langName]],
                        [
                            'proficiency_level' => ['beginner', 'intermediate', 'fluent', 'native'][rand(0, 3)],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }

        // Ensure nina.patel@email.com has NO interests and languages set (for onboarding testing)
        $ninaPatel = DB::table('users')->where('email', 'nina.patel@email.com')->first();
        if ($ninaPatel) {
            // Clear existing interests/languages first
            DB::table('user_interests')->where('user_id', $ninaPatel->id)->delete();
            DB::table('user_languages')->where('user_id', $ninaPatel->id)->delete();
            
            // Leave empty for onboarding testing
        }
    }

    /**
     * Seed featured stories for organizations
     */
    private function seedFeaturedStories($organizationIds)
    {
        foreach ($organizationIds as $orgId) {
            // Check if featured story already exists
            $existing = DB::table('featured_story')->where('organization_id', $orgId)->first();
            if ($existing) {
                continue;
            }

            // Get a random approved resident from this organization
            $resident = DB::selectOne("
                SELECT u.id, u.name
                FROM users u
                JOIN resident r ON u.id = r.user_id
                WHERE r.organization_id = ? AND r.status = 'approved'
                ORDER BY RAND()
                LIMIT 1
            ", [$orgId]);

            if ($resident) {
                $bios = [
                    "Meet {$resident->name}, a wonderful resident who loves sharing stories and connecting with others through letters. {$resident->name} has a passion for reading and enjoys discussing books with pen pals.",
                    "{$resident->name} is a kind-hearted individual who finds joy in gardening and nature. Through this platform, {$resident->name} hopes to connect with others who share similar interests.",
                    "We're excited to feature {$resident->name}, who brings warmth and wisdom to our community. {$resident->name} enjoys writing and receiving letters and looks forward to making new connections.",
                    "{$resident->name} is an active member of our community who loves cooking and sharing recipes. Through letter writing, {$resident->name} hopes to exchange culinary ideas and stories.",
                    "Meet {$resident->name}, a resident who finds fulfillment in art and creativity. {$resident->name} enjoys painting and would love to connect with others who appreciate the arts.",
                ];

                DB::table('featured_story')->insert([
                    'organization_id' => $orgId,
                    'resident_id' => $resident->id,
                    'bio' => $bios[array_rand($bios)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create unread letters between corresponding users for testing
     */
    private function seedUnreadLetters()
    {
        // Get some approved volunteers and residents
        $volunteers = DB::select("
            SELECT u.id, u.name, v.organization_id
            FROM users u
            JOIN volunteer v ON u.id = v.user_id
            WHERE v.status = 'approved'
            LIMIT 5
        ");

        $residents = DB::select("
            SELECT u.id, u.name, r.organization_id
            FROM users u
            JOIN resident r ON u.id = r.user_id
            WHERE r.status = 'approved'
            LIMIT 5
        ");

        if (empty($volunteers) || empty($residents)) {
            return;
        }

        $now = now();
        $unreadMessages = [
            "Hello! I hope this letter finds you well. I've been thinking about our last conversation and wanted to reach out.",
            "Thank you for your thoughtful letter! I really enjoyed reading about your experiences. I wanted to share something with you.",
            "I'm writing to check in and see how you're doing. I've been keeping you in my thoughts and wanted to send you a note.",
            "Hello! I have some exciting news I wanted to share with you. I hope you're doing well!",
            "I wanted to thank you for being such a wonderful pen pal. Your letters always brighten my day!",
        ];

        // Create unread letters: some from volunteers to residents, some from residents to volunteers
        for ($i = 0; $i < min(5, count($volunteers), count($residents)); $i++) {
            $sender = ($i % 2 === 0) ? $volunteers[$i] : $residents[$i];
            $receiver = ($i % 2 === 0) ? $residents[$i] : $volunteers[$i];

            // Create a letter that was delivered but not read (unread)
            $sentAt = $now->copy()->subDays(rand(1, 7))->subHours(rand(0, 23));
            $deliveredAt = $sentAt->copy()->addHours(8); // 8 hours after sent

            // Ensure delivered_at is in the past but read_at is null (unread)
            if ($deliveredAt->isPast()) {
                DB::table('letters')->insert([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'content' => $unreadMessages[$i % count($unreadMessages)],
                    'is_open_letter' => false,
                    'parent_letter_id' => null,
                    'status' => 'delivered', // Delivered but not read
                    'sent_at' => $sentAt,
                    'delivered_at' => $deliveredAt,
                    'read_at' => null, // Not read yet
                    'created_at' => $sentAt,
                    'updated_at' => $now,
                ]);
            }
        }

        // Also create some read letters between the same pairs to show correspondence history
        for ($i = 0; $i < min(3, count($volunteers), count($residents)); $i++) {
            $sender = ($i % 2 === 0) ? $volunteers[$i] : $residents[$i];
            $receiver = ($i % 2 === 0) ? $residents[$i] : $volunteers[$i];

            // Create older letters that were read (to show correspondence history)
            $sentAt = $now->copy()->subDays(rand(10, 30))->subHours(rand(0, 23));
            $deliveredAt = $sentAt->copy()->addHours(8);
            $readAt = $deliveredAt->copy()->addHours(rand(1, 24));

            if ($readAt->isPast()) {
                DB::table('letters')->insert([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'content' => $unreadMessages[($i + 2) % count($unreadMessages)],
                    'is_open_letter' => false,
                    'parent_letter_id' => null,
                    'status' => 'read',
                    'sent_at' => $sentAt,
                    'delivered_at' => $deliveredAt,
                    'read_at' => $readAt,
                    'created_at' => $sentAt,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Get a random status based on weights
     */
    private function getWeightedRandomStatus($statuses, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // fallback
    }
}
