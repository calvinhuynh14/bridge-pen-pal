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
            $orgId = DB::table('organization')->insertGetId([
                'name' => $org['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $organizationIds[] = $orgId;
        }

        // Get user type IDs
        $adminTypeId = DB::table('user_types')->where('name', 'admin')->value('id');
        $volunteerTypeId = DB::table('user_types')->where('name', 'volunteer')->value('id');
        $residentTypeId = DB::table('user_types')->where('name', 'resident')->value('id');

        // Create admin users for each organization
        $adminUsers = [];
        foreach ($organizationIds as $index => $orgId) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin ' . ($index + 1),
                'email' => 'admin' . ($index + 1) . '@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'user_type_id' => $adminTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('admin')->insert([
                'user_id' => $adminId,
                'organization_id' => $orgId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $volunteerNames[$i],
                'email' => $volunteerEmails[$i],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'user_type_id' => $volunteerTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign to random organization
            $orgId = $organizationIds[array_rand($organizationIds)];
            
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
            $orgId = $organizationIds[array_rand($organizationIds)]; // Random organization
            $status = ['pending', 'approved'][array_rand(['pending', 'approved'])]; // Mostly approved residents
            $applicationDate = now()->subDays(rand(1, 180)); // Random date in last 6 months
            
            // Generate 6-digit resident ID starting from 100000
            $residentId = 100000 + $i;
            
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
                'username' => (string)$residentId, // 6-digit username
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
        $this->command->info('Created 30 Residents.');

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 5 Organizations');
        $this->command->info('- 5 Admin Users');
        $this->command->info('- 50 Volunteer Applications');
        $this->command->info('- 30 Residents');
        $this->command->info('- Interest Categories & Interests');
        $this->command->info('- Languages');
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
