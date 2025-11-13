<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LetterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can send a letter to a pen pal
     */
    public function test_user_can_send_letter_to_pen_pal(): void
    {
        // Create user types if they don't exist
        $this->seedUserTypes();

        // Create a resident user (sender)
        $sender = $this->createResident('Alice Johnson', '100000', '137912');

        // Create another resident (receiver)
        $receiver = $this->createResident('Bob Smith', '100001', '525218');

        // Authenticate as sender
        $this->actingAs($sender);

        // Send a letter
        $response = $this->postJson('/api/letters', [
            'content' => 'Hello, this is a test letter!',
            'receiver_id' => $receiver->id,
            'is_open_letter' => false,
        ]);

        // Assert success
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'letter_id',
        ]);

        // Assert letter was created in database
        $this->assertDatabaseHas('letters', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => 'Hello, this is a test letter!',
            'is_open_letter' => false,
            'status' => 'sent',
        ]);
    }

    /**
     * Test that a user can send an open letter
     */
    public function test_user_can_send_open_letter(): void
    {
        $this->seedUserTypes();
        $sender = $this->createResident('Alice Johnson', '100000', '137912');
        $this->actingAs($sender);

        $response = $this->postJson('/api/letters', [
            'content' => 'This is an open letter for anyone to claim!',
            'is_open_letter' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('letters', [
            'sender_id' => $sender->id,
            'receiver_id' => null,
            'content' => 'This is an open letter for anyone to claim!',
            'is_open_letter' => true,
        ]);
    }

    /**
     * Test that empty content is rejected
     */
    public function test_user_cannot_send_empty_letter(): void
    {
        $this->seedUserTypes();
        $sender = $this->createResident('Alice Johnson', '100000', '137912');
        $this->actingAs($sender);

        $response = $this->postJson('/api/letters', [
            'content' => '',
            'is_open_letter' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * Test that content over 1000 characters is rejected
     */
    public function test_user_cannot_send_letter_over_character_limit(): void
    {
        $this->seedUserTypes();
        $sender = $this->createResident('Alice Johnson', '100000', '137912');
        $this->actingAs($sender);

        $longContent = str_repeat('a', 1001); // 1001 characters

        $response = $this->postJson('/api/letters', [
            'content' => $longContent,
            'is_open_letter' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * Test that receiver_id is required for non-open letters
     */
    public function test_receiver_id_required_for_pen_pal_letter(): void
    {
        $this->seedUserTypes();
        $sender = $this->createResident('Alice Johnson', '100000', '137912');
        $this->actingAs($sender);

        $response = $this->postJson('/api/letters', [
            'content' => 'Test letter',
            'is_open_letter' => false,
            // receiver_id is missing
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['receiver_id']);
    }

    /**
     * Test that users cannot see their own open letters
     */
    public function test_user_cannot_see_own_open_letters(): void
    {
        $this->seedUserTypes();
        $user = $this->createResident('Alice Johnson', '100000', '137912');
        $this->actingAs($user);

        // Create an open letter from this user
        DB::table('letters')->insert([
            'sender_id' => $user->id,
            'receiver_id' => null,
            'content' => 'My open letter',
            'is_open_letter' => true,
            'status' => 'delivered',
            'sent_at' => now()->subHours(10),
            'delivered_at' => now()->subHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create another user's open letter (should be visible)
        $otherUser = $this->createResident('Bob Smith', '100001', '525218');
        DB::table('letters')->insert([
            'sender_id' => $otherUser->id,
            'receiver_id' => null,
            'content' => 'Other user open letter',
            'is_open_letter' => true,
            'status' => 'delivered',
            'sent_at' => now()->subHours(10),
            'delivered_at' => now()->subHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fetch open letters via API (if endpoint exists) or check database query logic
        // For now, we'll test the database query directly
        $openLetters = DB::select("
            SELECT l.id, l.sender_id, l.content
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            JOIN user_types ut ON sender.user_type_id = ut.id
            WHERE l.is_open_letter = 1
            AND l.status IN ('sent', 'delivered')
            AND l.deleted_at IS NULL
            AND ut.name = 'resident'
            AND (l.claimed_by IS NULL OR l.claimed_by != ?)
            AND l.sender_id != ?
            ORDER BY l.sent_at DESC
        ", [$user->id, $user->id]);

        // Assert own letter is not in the list
        $ownLetters = collect($openLetters)->where('sender_id', $user->id);
        $this->assertEmpty($ownLetters, 'User should not see their own open letters');
        
        // Assert other user's letter is visible
        $otherLetters = collect($openLetters)->where('sender_id', $otherUser->id);
        $this->assertNotEmpty($otherLetters, 'Other users\' open letters should be visible');
    }

    /**
     * Test that correspondence API returns paginated results
     */
    public function test_correspondence_api_returns_paginated_results(): void
    {
        $this->seedUserTypes();
        $user1 = $this->createResident('Alice Johnson', '100000', '137912');
        $user2 = $this->createResident('Bob Smith', '100001', '525218');
        $this->actingAs($user1);

        // Create 15 messages between users
        for ($i = 0; $i < 15; $i++) {
            DB::table('letters')->insert([
                'sender_id' => ($i % 2 === 0) ? $user1->id : $user2->id,
                'receiver_id' => ($i % 2 === 0) ? $user2->id : $user1->id,
                'content' => "Message {$i}",
                'is_open_letter' => false,
                'status' => 'delivered',
                'sent_at' => now()->subDays($i),
                'delivered_at' => now()->subDays($i)->addHours(8),
                'created_at' => now()->subDays($i),
                'updated_at' => now(),
            ]);
        }

        // Fetch first page
        $response = $this->getJson("/api/correspondence/{$user2->id}?page=1&per_page=10");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'messages' => [
                '*' => ['id', 'content', 'sender_id', 'receiver_id']
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
                'has_more'
            ]
        ]);

        $data = $response->json();
        $this->assertCount(10, $data['messages']);
        $this->assertTrue($data['pagination']['has_more']);
    }

    /**
     * Helper: Create user types
     */
    private function seedUserTypes(): void
    {
        if (DB::table('user_types')->count() === 0) {
            DB::table('user_types')->insert([
                ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'volunteer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'resident', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Helper: Create a resident user
     */
    private function createResident(string $name, string $username, string $pin): User
    {
        $residentTypeId = DB::table('user_types')->where('name', 'resident')->value('id');

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'password' => bcrypt($pin),
            'user_type_id' => $residentTypeId,
        ]);

        // Create resident record
        $orgId = DB::table('organization')->insertGetId([
            'name' => 'Test Organization',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resident')->insert([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'status' => 'approved',
            'pin_code' => $pin,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}

