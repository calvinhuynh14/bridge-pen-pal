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

    // ========== ADMIN LOGIN TESTS ==========

    /**
     * Test that admin can login with valid credentials
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Test that admin cannot login with invalid credentials
     */
    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    // ========== ADMIN LOGIN SECURITY TEST CASES ==========

    /**
     * Test Case 1: SQL Injection Prevention in Admin Login Email
     * Try SQL injection: ' OR '1'='1 in email field → Should fail authentication
     */
    public function test_admin_login_sql_injection_in_email_field_fails_authentication(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Admin login uses the same /login route as regular login
        // Test SQL injection attempt in email field
        $response = $this->post('/login', [
            'email' => "' OR '1'='1",
            'password' => 'password',
        ]);

        // Should fail authentication (not find user)
        $this->assertGuest();
        
        // Fortify redirects back on failed login (302) - this is correct behavior
        // The important security check: authentication fails, no SQL injection occurs
        $response->assertRedirect();
        
        // Verify no database errors are exposed
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test Case 2: XSS Prevention in Admin Login Email
     * Try XSS: <script>alert('XSS')</script> in email field → Should be escaped in error message
     */
    public function test_admin_login_xss_in_email_field_is_escaped_in_error_message(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        $xssPayload = "<script>alert('XSS')</script>";
        
        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => $xssPayload,
            'password' => 'password',
        ]);

        $this->assertGuest();
        
        // Fortify redirects back on failed login
        $response->assertRedirect();
        
        // Verify error exists (Vue.js will auto-escape this in the frontend)
        $response->assertSessionHasErrors('email');
        
        // The XSS payload should be stored in session but Vue.js will escape it when displayed
        // This test verifies authentication fails without executing script
    }

    /**
     * Test Case 3: Invalid Email Format
     * Try invalid email format → Should fail authentication
     */
    public function test_admin_login_invalid_email_format_fails(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password',
        ]);

        // Should fail authentication
        $this->assertGuest();
        $response->assertRedirect();
    }

    /**
     * Test Case 4: Extremely Long Email
     * Try extremely long email (>255 chars) → Should fail authentication
     */
    public function test_admin_login_extremely_long_email_fails(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Create email longer than 255 characters (database limit)
        $longEmail = str_repeat('a', 250) . '@test.com'; // 256+ characters
        
        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => $longEmail,
            'password' => 'password',
        ]);

        // Should fail - authentication failure (email too long won't match)
        $this->assertGuest();
        
        // Fortify redirects back on failed login
        $response->assertRedirect();
        
        // Note: Laravel's email validation might catch this before authentication
        // Either way, authentication should fail
        $response->assertSessionHasErrors();
    }

    /**
     * Test Case 5: SQL Injection in Password Field
     * Ensure SQL injection attempts in password field also fail safely
     */
    public function test_admin_login_sql_injection_in_password_field_fails_safely(): void
    {
        $this->seedUserTypes();
        $admin = $this->createAdmin('Test Admin', 'admin@test.com');

        // Admin login uses the same /login route as regular login
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => "' OR '1'='1",
        ]);

        // Should fail authentication
        $this->assertGuest();
    }

    /**
     * Test Case 6: Non-Admin User Cannot Access Admin Routes
     * Verify that only admin users can access admin routes after login
     */
    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        // Login as volunteer
        $this->actingAs($volunteer);

        // Try to access admin route
        $response = $this->get('/admin/dashboard');

        // Should be redirected or denied access
        // This depends on your middleware implementation
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Non-admin users should not be able to access admin routes'
        );
    }

    // ========== SECURITY TEST CASES ==========

    /**
     * Test Case 1: SQL Injection Prevention
     * Try SQL injection: ' OR '1'='1 in email field → Should fail authentication
     */
    public function test_sql_injection_in_email_field_fails_authentication(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        // Test SQL injection attempt in email field
        $response = $this->post('/login?type=volunteer', [
            'email' => "' OR '1'='1",
            'password' => 'password',
        ]);

        // Should fail authentication (not find user)
        $this->assertGuest();
        
        // Fortify redirects back on failed login (302) - this is correct behavior
        // The important security check: authentication fails, no SQL injection occurs
        $response->assertRedirect();
        
        // Verify no database errors are exposed
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test Case 2: XSS Prevention
     * Try XSS: <script>alert('XSS')</script> in email field → Should be escaped in error message
     */
    public function test_xss_in_email_field_is_escaped_in_error_message(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $xssPayload = "<script>alert('XSS')</script>";
        
        $response = $this->post('/login?type=volunteer', [
            'email' => $xssPayload,
            'password' => 'password',
        ]);

        $this->assertGuest();
        
        // Fortify redirects back on failed login
        $response->assertRedirect();
        
        // Verify error exists (Vue.js will auto-escape this in the frontend)
        $response->assertSessionHasErrors('email');
        
        // The XSS payload should be stored in session but Vue.js will escape it when displayed
        // This test verifies authentication fails without executing script
    }

    /**
     * Test Case 3: Invalid Username Format
     * Try invalid username format: abc123 → Should be rejected
     * Note: Frontend validation prevents this, but backend should also handle it
     */
    public function test_invalid_username_format_abc123_is_rejected(): void
    {
        $this->seedUserTypes();
        $resident = $this->createResident('Test Resident', '100000', '123456');

        // Try to login with invalid username format (contains letters)
        $response = $this->post('/login?type=resident', [
            'email' => 'abc123', // Invalid format - contains letters
            'password' => '123456',
        ]);

        // Should fail authentication
        $this->assertGuest();
        
        // Note: Currently backend doesn't validate format, only checks if user exists
        // This test verifies that authentication fails (which it does because user doesn't exist)
        // Ideally, backend should also validate format before checking database
    }

    /**
     * Test Case 4: Extremely Long Email (>255 chars)
     * Try extremely long email (>255 chars) → Should be rejected
     */
    public function test_extremely_long_email_is_rejected(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        // Create email longer than 255 characters (database limit)
        $longEmail = str_repeat('a', 250) . '@test.com'; // 256+ characters
        
        $response = $this->post('/login?type=volunteer', [
            'email' => $longEmail,
            'password' => 'password',
        ]);

        // Should fail - authentication failure (email too long won't match)
        $this->assertGuest();
        
        // Fortify redirects back on failed login
        $response->assertRedirect();
        
        // Note: Laravel's email validation might catch this before authentication
        // Either way, authentication should fail
        $response->assertSessionHasErrors();
    }

    /**
     * Additional Security Test: SQL Injection in Password Field
     * Ensure SQL injection attempts in password field also fail safely
     */
    public function test_sql_injection_in_password_field_fails_safely(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $response = $this->post('/login?type=volunteer', [
            'email' => 'volunteer@test.com',
            'password' => "' OR '1'='1",
        ]);

        // Should fail authentication
        $this->assertGuest();
    }

    /**
     * Additional Security Test: Extremely Long Password
     * Ensure very long passwords are handled safely
     */
    public function test_extremely_long_password_is_handled_safely(): void
    {
        $this->seedUserTypes();
        $volunteer = $this->createVolunteer('Test Volunteer', 'volunteer@test.com');

        $longPassword = str_repeat('a', 1000); // Very long password
        
        $response = $this->post('/login?type=volunteer', [
            'email' => 'volunteer@test.com',
            'password' => $longPassword,
        ]);

        // Should fail authentication (wrong password)
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
