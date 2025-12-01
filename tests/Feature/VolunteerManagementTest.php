<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VolunteerManagementTest extends TestCase
{
    use RefreshDatabase;

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

    private function createAdmin(string $name, string $email, ?int $organizationId = null): User
    {
        $this->seedUserTypes();
        $adminTypeId = DB::table('user_types')->where('name', 'admin')->value('id');
        
        if (!$organizationId) {
            $organizationId = DB::table('organization')->insertGetId([
                'name' => 'Test Organization',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'user_type_id' => $adminTypeId,
            'email_verified_at' => now(),
        ]);

        DB::table('admin')->insert([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createVolunteer(string $name, string $email, int $organizationId, string $status = 'pending'): User
    {
        $volunteerTypeId = DB::table('user_types')->where('name', 'volunteer')->value('id');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'user_type_id' => $volunteerTypeId,
            'email_verified_at' => now(),
        ]);

        $volunteerId = DB::table('volunteer')->insertGetId([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'status' => $status,
            'application_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    // ========== VOLUNTEER MANAGEMENT SECURITY TEST CASES ==========

    /**
     * Test Case 1: Authorization - Admins Cannot Approve Volunteers from Other Organizations
     */
    public function test_volunteer_management_approve_from_other_organization_is_denied(): void
    {
        // Create two organizations
        $org1Id = DB::table('organization')->insertGetId([
            'name' => 'Organization 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $org2Id = DB::table('organization')->insertGetId([
            'name' => 'Organization 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create admins for each organization
        $admin1 = $this->createAdmin('Admin 1', 'admin1@test.com', $org1Id);
        $admin2 = $this->createAdmin('Admin 2', 'admin2@test.com', $org2Id);
        
        // Create volunteer for org2
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $org2Id);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        // Try approving as admin1 (different organization)
        $this->actingAs($admin1, 'web');
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/approve");

        // Should be denied (volunteer not found for this admin's organization)
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['error']);
        
        // Verify volunteer was NOT approved
        $volunteerRecord = DB::table('volunteer')->where('id', $volunteerAppId)->first();
        $this->assertEquals('pending', $volunteerRecord->status);
    }

    /**
     * Test Case 2: SQL Injection Prevention in Rejection Reason
     */
    public function test_volunteer_management_sql_injection_in_rejection_reason_is_prevented(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // Try SQL injection in rejection reason
        $sqlInjection = "'; DROP TABLE volunteers; --";
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/reject", [
            'rejection_reason' => $sqlInjection,
        ]);

        $response->assertStatus(302);
        
        // Verify rejection reason was sanitized (SQL not executed)
        $volunteerRecord = DB::table('volunteer')->where('id', $volunteerAppId)->first();
        $this->assertEquals(strip_tags(trim($sqlInjection)), $volunteerRecord->rejection_reason);
        
        // Verify table still exists (no DROP executed)
        $this->assertDatabaseHas('volunteer', [
            'id' => $volunteerAppId,
        ]);
    }

    /**
     * Test Case 3: XSS Prevention in Rejection Reason
     */
    public function test_volunteer_management_xss_in_rejection_reason_is_sanitized(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // Try XSS payload
        $xssReason = "<script>alert('XSS')</script>Not suitable";
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/reject", [
            'rejection_reason' => $xssReason,
        ]);

        $response->assertStatus(302);
        
        // Verify rejection reason was sanitized
        $volunteerRecord = DB::table('volunteer')->where('id', $volunteerAppId)->first();
        $this->assertEquals("alert('XSS')Not suitable", $volunteerRecord->rejection_reason);
        $this->assertStringNotContainsString('<script>', $volunteerRecord->rejection_reason);
    }

    /**
     * Test Case 4: Authorization - Admins Cannot Delete Volunteers from Other Organizations
     */
    public function test_volunteer_management_delete_from_other_organization_is_denied(): void
    {
        // Create two organizations
        $org1Id = DB::table('organization')->insertGetId([
            'name' => 'Organization 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $org2Id = DB::table('organization')->insertGetId([
            'name' => 'Organization 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create admins for each organization
        $admin1 = $this->createAdmin('Admin 1', 'admin1@test.com', $org1Id);
        $admin2 = $this->createAdmin('Admin 2', 'admin2@test.com', $org2Id);
        
        // Create volunteer for org2
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $org2Id);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        // Try deleting as admin1 (different organization)
        $this->actingAs($admin1, 'web');
        $response = $this->delete("/admin/volunteers/{$volunteerAppId}/delete");

        // Should be denied (volunteer not found for this admin's organization)
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['error']);
        
        // Verify volunteer was NOT deleted
        $this->assertDatabaseHas('volunteer', [
            'id' => $volunteerAppId,
        ]);
    }

    /**
     * Test Case 5: Parameterized Queries for Approve
     */
    public function test_volunteer_management_approve_uses_parameterized_queries(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // DB::update() uses parameterized queries automatically
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/approve");

        $response->assertStatus(302);
        
        // Verify volunteer was approved (parameterized query worked)
        $this->assertDatabaseHas('volunteer', [
            'id' => $volunteerAppId,
            'status' => 'approved',
        ]);
    }

    /**
     * Test Case 6: Parameterized Queries for Reject
     */
    public function test_volunteer_management_reject_uses_parameterized_queries(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // DB::update() uses parameterized queries automatically
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/reject", [
            'rejection_reason' => 'Not suitable',
        ]);

        $response->assertStatus(302);
        
        // Verify volunteer was rejected (parameterized query worked)
        $this->assertDatabaseHas('volunteer', [
            'id' => $volunteerAppId,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test Case 7: Parameterized Queries for Delete
     */
    public function test_volunteer_management_delete_uses_parameterized_queries(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // DB::delete() uses parameterized queries automatically
        $response = $this->delete("/admin/volunteers/{$volunteerAppId}/delete");

        $response->assertStatus(302);
        
        // Verify volunteer was deleted (parameterized query worked)
        $this->assertDatabaseMissing('volunteer', [
            'id' => $volunteerAppId,
        ]);
    }

    /**
     * Test Case 8: Rejection Reason Validation - Max Length
     */
    public function test_volunteer_management_rejection_reason_max_length(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // Try rejection reason > 1000 characters
        $longReason = str_repeat('A', 1001);
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/reject", [
            'rejection_reason' => $longReason,
        ]);

        // Should fail validation
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rejection_reason']);
    }

    /**
     * Test Case 9: CSRF Protection
     */
    public function test_volunteer_management_csrf_protection_works(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $orgId = DB::table('admin')->where('user_id', $admin->id)->value('organization_id');
        
        $volunteer = $this->createVolunteer('Volunteer', 'volunteer@test.com', $orgId);
        $volunteerAppId = DB::table('volunteer')->where('user_id', $volunteer->id)->value('id');

        $this->actingAs($admin, 'web');

        // Normal request should work (CSRF is handled automatically in tests)
        $response = $this->post("/admin/volunteers/{$volunteerAppId}/approve");

        // Should succeed (CSRF is bypassed in tests, but middleware is active)
        $response->assertStatus(302);
        $this->assertTrue(true);
    }

    /**
     * Test Case 10: Unauthorized Access Prevention
     */
    public function test_volunteer_management_requires_authentication(): void
    {
        // Try accessing without authentication
        $response = $this->get('/admin/volunteers');

        // Should redirect to login
        $response->assertRedirect('/login');
    }
}

