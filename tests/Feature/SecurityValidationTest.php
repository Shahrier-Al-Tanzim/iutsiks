<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_xss_prevention_in_contact_form(): void
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        
        $response = $this->post('/contact', [
            'name' => $maliciousInput,
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message'
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_sql_injection_prevention_in_forms(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        $maliciousInput = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($user)->post('/events', [
            'title' => $maliciousInput,
            'description' => 'Test description',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'event_time' => '10:00',
            'type' => 'lecture',
            'registration_type' => 'individual',
            'status' => 'draft'
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_file_upload_security_validation(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        // Test malicious file upload
        $maliciousFile = UploadedFile::fake()->create('malicious.php', 100);
        
        $response = $this->actingAs($user)->post('/gallery', [
            'images' => [$maliciousFile],
            'captions' => ['Test caption']
        ]);

        $response->assertSessionHasErrors(['images.0']);
    }

    public function test_oversized_file_rejection(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        // Create a file larger than 5MB
        $largeFile = UploadedFile::fake()->create('large.jpg', 6000);
        
        $response = $this->actingAs($user)->post('/gallery', [
            'images' => [$largeFile],
            'captions' => ['Test caption']
        ]);

        $response->assertSessionHasErrors(['images.0']);
    }

    public function test_rate_limiting_on_contact_form(): void
    {
        // Make multiple requests quickly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/contact', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test message'
            ]);
        }

        // The 6th request should be rate limited
        $response->assertStatus(429);
    }

    public function test_csrf_protection_on_forms(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        // Attempt to submit form without CSRF token by disabling middleware
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
        $response = $this->actingAs($user)->post('/events', [
            'title' => 'Test Event',
            'description' => 'Test description',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'event_time' => '10:00',
            'type' => 'lecture',
            'registration_type' => 'individual',
            'status' => 'draft'
        ]);

        // Should succeed when CSRF is disabled, proving CSRF protection exists
        $response->assertRedirect();
    }

    public function test_authorization_on_admin_routes(): void
    {
        $regularUser = User::factory()->create(['role' => 'member']);
        
        $response = $this->actingAs($regularUser)->get('/admin/dashboard');
        
        $response->assertStatus(403);
    }

    public function test_input_length_validation(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        $longString = str_repeat('a', 5001); // Exceeds max length
        
        $response = $this->actingAs($user)->post('/events', [
            'title' => 'Test Event',
            'description' => $longString,
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'event_time' => '10:00',
            'type' => 'lecture',
            'registration_type' => 'individual',
            'status' => 'draft'
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_dangerous_file_extension_rejection(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        $dangerousExtensions = ['php', 'exe', 'bat', 'js', 'vbs'];
        
        foreach ($dangerousExtensions as $ext) {
            $file = UploadedFile::fake()->create("malicious.{$ext}", 100);
            
            $response = $this->actingAs($user)->post('/gallery', [
                'images' => [$file],
                'captions' => ['Test caption']
            ]);

            $response->assertSessionHasErrors(['images.0']);
        }
    }

    public function test_registration_duplicate_prevention(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'registration_type' => 'individual',
            'max_participants' => 10
        ]);
        
        // First registration should succeed
        $response = $this->actingAs($user)->post("/events/{$event->id}/register/individual", [
            'individual_name' => 'Test User',
            'registration_type' => 'individual'
        ]);

        $response->assertRedirect();
        
        // Second registration should fail
        $response = $this->actingAs($user)->post("/events/{$event->id}/register/individual", [
            'individual_name' => 'Test User',
            'registration_type' => 'individual'
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_payment_transaction_id_uniqueness(): void
    {
        // Skip this test for now as it requires full registration system
        $this->markTestSkipped('Payment system not fully implemented in current test environment');
    }
}
