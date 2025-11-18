<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Fest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guests_can_view_published_fests()
    {
        $fest = Fest::factory()->create(['status' => 'published']);

        $response = $this->get(route('fests.index'));

        $response->assertStatus(200);
        $response->assertSee($fest->title);
    }

    public function test_guests_cannot_see_draft_fests()
    {
        $fest = Fest::factory()->create(['status' => 'draft']);

        $response = $this->get(route('fests.index'));

        $response->assertStatus(200);
        $response->assertDontSee($fest->title);
    }

    public function test_admin_can_create_fest()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post(route('fests.store'), [
            'title' => 'Test Fest',
            'description' => 'This is a test fest description that is long enough to meet the minimum requirements.',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'published'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fests', [
            'title' => 'Test Fest',
            'created_by' => $admin->id
        ]);
    }

    public function test_regular_user_cannot_create_fest()
    {
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($user)->post(route('fests.store'), [
            'title' => 'Test Fest',
            'description' => 'This is a test fest description that is long enough to meet the minimum requirements.',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'published'
        ]);

        $response->assertStatus(403);
    }

    public function test_fest_creation_with_banner_image()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $file = UploadedFile::fake()->image('banner.jpg');

        $response = $this->actingAs($admin)->post(route('fests.store'), [
            'title' => 'Test Fest',
            'description' => 'This is a test fest description that is long enough to meet the minimum requirements.',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'published',
            'banner_image' => $file
        ]);

        $response->assertRedirect();
        
        $fest = Fest::where('title', 'Test Fest')->first();
        $this->assertNotNull($fest->banner_image);
        Storage::disk('public')->assertExists($fest->banner_image);
    }

    public function test_fest_validation_rules()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post(route('fests.store'), [
            'title' => '', // Required
            'description' => 'Short', // Too short
            'start_date' => now()->subDay()->format('Y-m-d'), // Past date
            'end_date' => now()->subDays(2)->format('Y-m-d'), // Before start date
            'status' => 'invalid' // Invalid status
        ]);

        $response->assertSessionHasErrors(['title', 'description', 'start_date', 'end_date', 'status']);
    }

    public function test_admin_can_update_fest()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $fest = Fest::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->put(route('fests.update', $fest), [
            'title' => 'Updated Fest Title',
            'description' => 'This is an updated fest description that is long enough to meet the minimum requirements.',
            'start_date' => $fest->start_date->format('Y-m-d'),
            'end_date' => $fest->end_date->format('Y-m-d'),
            'status' => 'completed'
        ]);

        $response->assertRedirect();
        $fest->refresh();
        $this->assertEquals('Updated Fest Title', $fest->title);
        $this->assertEquals('completed', $fest->status);
    }

    public function test_admin_can_delete_fest()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $fest = Fest::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->delete(route('fests.destroy', $fest));

        $response->assertRedirect(route('fests.index'));
        $this->assertDatabaseMissing('fests', ['id' => $fest->id]);
    }
}