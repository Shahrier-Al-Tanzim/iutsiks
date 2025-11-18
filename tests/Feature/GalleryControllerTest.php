<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Fest;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_gallery_index_page_loads()
    {
        $response = $this->get(route('gallery.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('gallery.index');
        $response->assertSee('Gallery');
    }

    public function test_gallery_index_shows_images()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $event = Event::factory()->create();
        
        $image = GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
            'uploaded_by' => $user->id,
            'caption' => 'Test Image Caption'
        ]);

        $response = $this->get(route('gallery.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Test Image Caption');
    }

    public function test_gallery_show_page_for_event()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $event = Event::factory()->create(['title' => 'Test Event']);
        
        $image = GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
            'uploaded_by' => $user->id
        ]);

        $response = $this->get(route('gallery.show', ['type' => 'event', 'id' => $event->id]));
        
        $response->assertStatus(200);
        $response->assertViewIs('gallery.show');
        $response->assertSee('Test Event');
    }

    public function test_gallery_show_page_for_fest()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $fest = Fest::factory()->create(['title' => 'Test Fest']);
        
        $image = GalleryImage::factory()->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $fest->id,
            'uploaded_by' => $user->id
        ]);

        $response = $this->get(route('gallery.show', ['type' => 'fest', 'id' => $fest->id]));
        
        $response->assertStatus(200);
        $response->assertViewIs('gallery.show');
        $response->assertSee('Test Fest');
    }

    public function test_admin_can_access_upload_form()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        
        $response = $this->actingAs($admin)->get(route('gallery.create'));
        
        $response->assertStatus(200);
        $response->assertViewIs('gallery.create');
        $response->assertSee('Upload Images');
    }

    public function test_non_admin_cannot_access_upload_form()
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $response = $this->actingAs($user)->get(route('gallery.create'));
        
        $response->assertStatus(403);
    }

    public function test_admin_can_upload_images()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $event = Event::factory()->create();
        
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $response = $this->actingAs($admin)->post(route('gallery.store'), [
            'images' => [$file],
            'association_type' => 'event',
            'association_id' => $event->id,
            'captions' => ['Test caption']
        ]);
        
        $response->assertRedirect(route('gallery.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('gallery_images', [
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
            'uploaded_by' => $admin->id,
            'caption' => 'Test caption'
        ]);
    }

    public function test_admin_can_edit_image()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $image = GalleryImage::factory()->create([
            'uploaded_by' => $admin->id,
            'caption' => 'Original caption'
        ]);
        
        $response = $this->actingAs($admin)->get(route('gallery.edit', $image));
        
        $response->assertStatus(200);
        $response->assertViewIs('gallery.edit');
        $response->assertSee('Original caption');
    }

    public function test_admin_can_update_image()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $image = GalleryImage::factory()->create([
            'uploaded_by' => $admin->id,
            'caption' => 'Original caption'
        ]);
        
        $response = $this->actingAs($admin)->put(route('gallery.update', $image), [
            'caption' => 'Updated caption',
            'alt_text' => 'Updated alt text',
            'association_type' => 'general'
        ]);
        
        $response->assertRedirect(route('gallery.index'));
        $response->assertSessionHas('success');
        
        $image->refresh();
        $this->assertEquals('Updated caption', $image->caption);
        $this->assertEquals('Updated alt text', $image->alt_text);
    }

    public function test_admin_can_delete_image()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $image = GalleryImage::factory()->create([
            'uploaded_by' => $admin->id
        ]);
        
        $response = $this->actingAs($admin)->delete(route('gallery.destroy', $image));
        
        $response->assertRedirect(route('gallery.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('gallery_images', [
            'id' => $image->id
        ]);
    }

    public function test_image_data_api_returns_correct_format()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $event = Event::factory()->create(['title' => 'Test Event']);
        
        $image = GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
            'uploaded_by' => $user->id,
            'caption' => 'Test Caption',
            'alt_text' => 'Test Alt Text'
        ]);

        $response = $this->get(route('gallery.image-data', $image));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'url',
            'thumbnail',
            'caption',
            'alt_text',
            'uploader',
            'uploaded_at',
            'file_size',
            'imageable'
        ]);
        
        $data = $response->json();
        $this->assertEquals('Test Caption', $data['caption']);
        $this->assertEquals('Test Alt Text', $data['alt_text']);
        $this->assertEquals($user->name, $data['uploader']);
    }

    public function test_gallery_filtering_works()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $event = Event::factory()->create();
        $fest = Fest::factory()->create();
        
        // Create images for different types
        GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
            'uploaded_by' => $user->id
        ]);
        
        GalleryImage::factory()->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $fest->id,
            'uploaded_by' => $user->id
        ]);
        
        GalleryImage::factory()->create([
            'imageable_type' => null,
            'imageable_id' => null,
            'uploaded_by' => $user->id
        ]);

        // Test event filter
        $response = $this->get(route('gallery.index', ['type' => 'events']));
        $response->assertStatus(200);
        
        // Test fest filter
        $response = $this->get(route('gallery.index', ['type' => 'fests']));
        $response->assertStatus(200);
        
        // Test general filter
        $response = $this->get(route('gallery.index', ['type' => 'general']));
        $response->assertStatus(200);
    }

    public function test_gallery_widget_returns_recent_images()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        GalleryImage::factory()->count(3)->create([
            'uploaded_by' => $user->id
        ]);

        $response = $this->get(route('gallery.widget', ['type' => 'recent', 'limit' => 2]));
        
        $response->assertStatus(200);
        $response->assertViewIs('components.gallery-widget');
    }
}