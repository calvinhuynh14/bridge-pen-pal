<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResidentManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can create a resident with valid data (PASS)
     */
    public function test_admin_can_create_resident(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin User', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'room_number' => '101',
            'floor_number' => '1',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302); // Redirect after creation
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'username' => '100001',
            'name' => 'New Resident',
        ]);
        
        $this->assertDatabaseHas('resident', [
            'pin_code' => '123456',
            'room_number' => '101',
            'floor_number' => '1',
        ]);
    }

    /**
     * Test that name is required (FAIL)
     */
    public function test_resident_name_is_required(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => '',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test that name cannot exceed 255 characters (FAIL)
     */
    public function test_resident_name_cannot_exceed_255_characters(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $longName = str_repeat('a', 256); // 256 characters
        
        $response = $this->post('/admin/residents', [
            'name' => $longName,
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test that username must be exactly 6 digits (FAIL)
     */
    public function test_resident_username_must_be_6_digits(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        // Test with 5 digits
        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '10000', // Only 5 digits
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username']);
        
        // Test with 7 digits
        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '1000000', // 7 digits
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username']);
    }

    /**
     * Test that username must be numeric only (FAIL)
     */
    public function test_resident_username_must_be_numeric(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => 'abc123', // Contains letters
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username']);
    }

    /**
     * Test that username cannot be duplicate (FAIL)
     */
    public function test_resident_username_must_be_unique(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        // Create first resident
        $this->post('/admin/residents', [
            'name' => 'First Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        // Try to create another with same username
        $response = $this->post('/admin/residents', [
            'name' => 'Second Resident',
            'username' => '100001', // Duplicate
            'date_of_birth' => '1990-01-01',
            'pin_code' => '654321',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['username']);
    }

    /**
     * Test that date of birth is required (FAIL)
     */
    public function test_resident_date_of_birth_is_required(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['date_of_birth']);
    }

    /**
     * Test that date of birth must be valid format (FAIL)
     */
    public function test_resident_date_of_birth_must_be_valid_format(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '01-01-1990', // Wrong format
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['date_of_birth']);
    }

    /**
     * Test that PIN code must be exactly 6 digits (FAIL)
     */
    public function test_resident_pin_must_be_6_digits(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        // Test with 5 digits
        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '12345', // Only 5 digits
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['pin_code']);
        
        // Test with 7 digits
        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '1234567', // 7 digits
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['pin_code']);
    }

    /**
     * Test that PIN code must be numeric only (FAIL)
     */
    public function test_resident_pin_must_be_numeric(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => 'abc123', // Contains letters
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['pin_code']);
    }

    /**
     * Test that room number is optional (PASS)
     */
    public function test_resident_room_number_is_optional(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'room_number' => '', // Optional
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    /**
     * Test that room number max 10 characters (PASS)
     */
    public function test_resident_room_number_max_10_characters(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'room_number' => str_repeat('a', 11), // 11 characters - should fail
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['room_number']);
    }

    /**
     * Test that floor number is optional (PASS)
     */
    public function test_resident_floor_number_is_optional(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'floor_number' => '', // Optional
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    /**
     * Test that application_date is set automatically (PASS)
     */
    public function test_resident_application_date_is_set_automatically(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        
        $resident = DB::table('resident')
            ->join('users', 'resident.user_id', '=', 'users.id')
            ->where('users.username', '100001')
            ->first();
        
        $this->assertNotNull($resident->application_date);
        $this->assertInstanceOf(\DateTime::class, new \DateTime($resident->application_date));
    }

    /**
     * Test that email is null for residents (PASS)
     */
    public function test_resident_email_is_null(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        
        $user = DB::table('users')->where('username', '100001')->first();
        $this->assertNull($user->email);
    }

    /**
     * Test that username is stored in users table (PASS)
     */
    public function test_resident_username_stored_in_users_table(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('users', [
            'username' => '100001',
            'name' => 'New Resident',
        ]);
    }

    /**
     * Test that status is 'approved' by default (PASS)
     */
    public function test_resident_status_is_approved_by_default(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Admin', 'admin@test.com');
        $this->actingAs($admin);

        $response = $this->post('/admin/residents', [
            'name' => 'New Resident',
            'username' => '100001',
            'date_of_birth' => '1990-01-01',
            'pin_code' => '123456',
        ]);

        $response->assertStatus(302);
        
        $resident = DB::table('resident')
            ->join('users', 'resident.user_id', '=', 'users.id')
            ->where('users.username', '100001')
            ->first();
        
        $this->assertEquals('approved', $resident->status);
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

    private function createAdmin(string $name, string $email): User
    {
        $adminTypeId = DB::table('user_types')->where('name', 'admin')->value('id');
        
        $orgId = DB::table('organization')->insertGetId([
            'name' => 'Test Organization',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'user_type_id' => $adminTypeId,
        ]);

        DB::table('admin')->insert([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}


