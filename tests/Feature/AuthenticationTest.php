<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    // ========== RESIDENT LOGIN TESTS ==========

    /**
     * Test that resident can login with 6-digit username and PIN (PASS)
     * Note: Frontend sends username as 'email' field for Fortify compatibility
     */
    public function test_resident_can_login_with_valid_credentials(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        $response = $this->post('/login?type=resident', [
            'email' => '100000', // Username sent as 'email' field
            'password' => '123456',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test that username must be exactly 6 digits (FAIL)
     */
    public function test_resident_username_must_be_6_digits(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        // Test with 5 digits
        $response = $this->post('/login?type=resident', [
            'email' => '10000', // Only 5 digits
            'password' => '123456',
        ]);

        $this->assertGuest();
        
        // Test with 7 digits
        $response = $this->post('/login?type=resident', [
            'email' => '1000000', // 7 digits
            'password' => '123456',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that username must be numeric only (FAIL)
     */
    public function test_resident_username_must_be_numeric(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        $response = $this->post('/login?type=resident', [
            'email' => 'abc123', // Contains letters
            'password' => '123456',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that PIN must be exactly 6 digits (FAIL)
     */
    public function test_resident_pin_must_be_6_digits(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        // Test with 5 digits
        $response = $this->post('/login?type=resident', [
            'email' => '100000',
            'password' => '12345', // Only 5 digits
        ]);

        $this->assertGuest();
        
        // Test with 7 digits
        $response = $this->post('/login?type=resident', [
            'email' => '100000',
            'password' => '1234567', // 7 digits
        ]);

        $this->assertGuest();
    }

    /**
     * Test that PIN must be numeric only (FAIL)
     */
    public function test_resident_pin_must_be_numeric(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        $response = $this->post('/login?type=resident', [
            'email' => '100000',
            'password' => 'abc123', // Contains letters
        ]);

        $this->assertGuest();
    }

    /**
     * Test that invalid username is rejected (FAIL)
     */
    public function test_resident_cannot_login_with_invalid_username(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        $response = $this->post('/login?type=resident', [
            'email' => '999999', // Non-existent username
            'password' => '123456',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that invalid PIN is rejected (FAIL)
     */
    public function test_resident_cannot_login_with_invalid_pin(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        $response = $this->post('/login?type=resident', [
            'email' => '100000',
            'password' => '999999', // Wrong PIN
        ]);

        $this->assertGuest();
    }

    // ========== VOLUNTEER/ADMIN LOGIN TESTS ==========

    /**
     * Test that email is required for volunteer/admin login (FAIL)
     */
    public function test_volunteer_email_is_required(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => '',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that email must be valid format (FAIL)
     */
    public function test_volunteer_email_must_be_valid_format(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => 'invalid-email', // Not a valid email
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that password is required (FAIL)
     */
    public function test_volunteer_password_is_required(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => 'volunteer@test.com',
            'password' => '',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that invalid email is rejected (FAIL)
     */
    public function test_volunteer_cannot_login_with_invalid_email(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => 'wrong@test.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /**
     * Test that invalid password is rejected (FAIL)
     */
    public function test_volunteer_cannot_login_with_invalid_password(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => 'volunteer@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
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
}
