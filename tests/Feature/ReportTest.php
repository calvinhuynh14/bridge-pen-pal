<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can report a letter with valid reason (PASS)
     */
    public function test_user_can_report_letter_with_valid_reason(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter User', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported User', '100000', '123456');
        
        $letter = $this->createLetter($reportedUser->id, 'Inappropriate content here');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => 'This letter contains inappropriate content that violates community guidelines.',
        ]);

        $response->assertStatus(302); // Redirect after success
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reportedUser->id,
            'reported_letter_id' => $letter,
            'status' => 'pending',
        ]);
    }

    /**
     * Test that reason must be at least 20 characters (FAIL)
     */
    public function test_report_reason_must_be_at_least_20_characters(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => 'Too short', // Only 9 characters
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
        
        $this->assertDatabaseMissing('reports', [
            'reported_letter_id' => $letter,
        ]);
    }

    /**
     * Test that reason cannot exceed 500 characters (FAIL)
     */
    public function test_report_reason_cannot_exceed_500_characters(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $longReason = str_repeat('a', 501); // 501 characters
        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => $longReason,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
    }

    /**
     * Test that reason is required (FAIL)
     */
    public function test_report_reason_is_required(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
    }

    /**
     * Test that reason cannot be only whitespace (FAIL)
     */
    public function test_report_reason_cannot_be_only_whitespace(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => '                    ', // Only whitespace
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
    }

    /**
     * Test that reason can be exactly 20 characters (PASS)
     */
    public function test_report_reason_can_be_exactly_20_characters(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => str_repeat('a', 20), // Exactly 20 characters
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    /**
     * Test that reason can be exactly 500 characters (PASS)
     */
    public function test_report_reason_can_be_exactly_500_characters(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => str_repeat('a', 500), // Exactly 500 characters
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    /**
     * Test that users cannot report their own letters (FAIL)
     */
    public function test_user_cannot_report_own_letter(): void
    {
        $this->seedUserTypes();
        
        $user = $this->createResident('User', '100000', '123456');
        $letter = $this->createLetter($user->id, 'My own letter');
        
        $this->actingAs($user);

        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => 'This is a valid reason with more than twenty characters.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['message']);
        
        $this->assertDatabaseMissing('reports', [
            'reported_letter_id' => $letter,
        ]);
    }

    /**
     * Test that report reason is sanitized (XSS protection) (PASS)
     */
    public function test_report_reason_is_sanitized(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $maliciousReason = '<script>alert("XSS")</script>This is a valid reason with more than twenty characters.';
        
        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => $maliciousReason,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        
        // Check that HTML tags are stripped
        $report = DB::table('reports')
            ->where('reported_letter_id', $letter)
            ->first();
        
        $this->assertStringNotContainsString('<script>', $report->reason);
        $this->assertStringNotContainsString('</script>', $report->reason);
    }

    /**
     * Test that report stores correct data (PASS)
     */
    public function test_report_stores_correct_data(): void
    {
        $this->seedUserTypes();
        
        $reporter = $this->createVolunteer('Reporter', 'reporter@test.com');
        $reportedUser = $this->createResident('Reported', '100000', '123456');
        $letter = $this->createLetter($reportedUser->id, 'Test letter');
        
        $this->actingAs($reporter);

        $reason = 'This is a valid reason with more than twenty characters.';
        $response = $this->post("/platform/letters/{$letter}/report", [
            'reason' => $reason,
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reportedUser->id,
            'reported_letter_id' => $letter,
            'status' => 'pending',
        ]);
        
        $report = DB::table('reports')
            ->where('reported_letter_id', $letter)
            ->first();
        
        $this->assertEquals($reason, $report->reason);
        $this->assertNotNull($report->created_at);
    }

    // ========== HELPER METHODS ==========

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

    private function createResident(string $name, string $username, string $pin): User
    {
        $residentTypeId = DB::table('user_types')->where('name', 'resident')->value('id');

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'password' => bcrypt($pin),
            'user_type_id' => $residentTypeId,
        ]);

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

    private function createVolunteer(string $name, string $email): User
    {
        $volunteerTypeId = DB::table('user_types')->where('name', 'volunteer')->value('id');
        
        $orgId = DB::table('organization')->insertGetId([
            'name' => 'Test Organization',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'user_type_id' => $volunteerTypeId,
        ]);

        DB::table('volunteer')->insert([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createLetter(int $senderId, string $content): int
    {
        return DB::table('letters')->insertGetId([
            'sender_id' => $senderId,
            'receiver_id' => null,
            'content' => $content,
            'is_open_letter' => true,
            'status' => 'delivered',
            'sent_at' => now()->subHours(10),
            'delivered_at' => now()->subHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}


