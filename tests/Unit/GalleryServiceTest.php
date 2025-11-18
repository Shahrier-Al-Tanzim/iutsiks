<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GalleryService;
use App\Models\GalleryImage;
use App\Models\Event;
use App\Models\Fest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GalleryServiceTest extends TestCase
{
    use RefreshDatabase;

    private GalleryService $galleryService;
    private User $user;
    private Event $event;
    private Fest $fest;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->galleryService = new GalleryService();
        
        $this->user = User::factory()->create(['role' => 'super_admin']);
        $this->fest = Fest::factory()->create();
        $this->event = Event::factory()->create(['fest_id' => $this->fest->id]);
        
        // Fake the storage disk
        Storage::fake('public');
    }

    public function test_upload_single_image_for_event()
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        
        $options = [
            'caption' => 'Test event image',
            'alt_text' => 'Test alt text',
        ];

        $galleryImage = $this->galleryService->uploadSingleImage($file, $this->event, $this->user, $options);

        $this->assertInstanceOf(GalleryImage::class, $galleryImage);
        $this->assertEquals($this->event->id, $galleryImage->imageable_id);
        $this->assertEquals(Event::class, $galleryImage->imageable_type);
        $this->assertEquals($this->user->id, $galleryImage->uploaded_by);
        $this->assertEquals('Test event image', $galleryImage->caption);
        $this->assertEquals('Test alt text', $galleryImage->alt_text);
        $this->assertNotNull($galleryImage->image_path);
        
        // Check that file was stored
        Storage::disk('public')->assertExists($galleryImage->image_path);
    }

    public function test_upload_single_image_for_fest()
    {
        $file = UploadedFile::fake()->image('fest.jpg', 800, 600);

        $galleryImage = $this->galleryService->uploadSingleImage($file, $this->fest, $this->user);

        $this->assertEquals($this->fest->id, $galleryImage->imageable_id);
        $this->assertEquals(Fest::class, $galleryImage->imageable_type);
        $this->assertStringContainsString('fests', $galleryImage->image_path);
    }

    public function test_upload_single_image_for_general_gallery()
    {
        $file = UploadedFile::fake()->image('general.jpg', 800, 600);

        $galleryImage = $this->galleryService->uploadSingleImage($file, null, $this->user);

        $this->assertNull($galleryImage->imageable_id);
        $this->assertNull($galleryImage->imageable_type);
        $this->assertStringContainsString('general', $galleryImage->image_path);
    }

    public function test_upload_multiple_images()
    {
        $files = [
            UploadedFile::fake()->image('image1.jpg', 800, 600),
            UploadedFile::fake()->image('image2.jpg', 800, 600),
            UploadedFile::fake()->image('image3.jpg', 800, 600),
        ];

        $uploadedImages = $this->galleryService->uploadImages($files, $this->event, $this->user);

        $this->assertCount(3, $uploadedImages);
        
        foreach ($uploadedImages as $image) {
            $this->assertInstanceOf(GalleryImage::class, $image);
            $this->assertEquals($this->event->id, $image->imageable_id);
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_upload_image_validates_file_size()
    {
        // Create a file larger than 5MB (mock)
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB

        $galleryImage = $this->galleryService->uploadSingleImage($file, $this->event, $this->user);

        $this->assertNull($galleryImage);
    }

    public function test_get_event_gallery()
    {
        // Create gallery images for the event
        GalleryImage::factory()->count(3)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);
        
        // Create images for other events (should not be included)
        $otherEvent = Event::factory()->create();
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $otherEvent->id,
        ]);

        $gallery = $this->galleryService->getEventGallery($this->event);

        $this->assertCount(3, $gallery);
        
        foreach ($gallery as $image) {
            $this->assertEquals($this->event->id, $image->imageable_id);
            $this->assertEquals(Event::class, $image->imageable_type);
        }
    }

    public function test_get_fest_gallery()
    {
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $this->fest->id,
        ]);

        $gallery = $this->galleryService->getFestGallery($this->fest);

        $this->assertCount(2, $gallery);
        
        foreach ($gallery as $image) {
            $this->assertEquals($this->fest->id, $image->imageable_id);
            $this->assertEquals(Fest::class, $image->imageable_type);
        }
    }

    public function test_get_general_gallery()
    {
        GalleryImage::factory()->count(4)->create([
            'imageable_type' => null,
            'imageable_id' => null,
        ]);
        
        // Create some event images (should not be included)
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);

        $gallery = $this->galleryService->getGeneralGallery();

        $this->assertCount(4, $gallery);
        
        foreach ($gallery as $image) {
            $this->assertNull($image->imageable_id);
            $this->assertNull($image->imageable_type);
        }
    }

    public function test_get_all_gallery_images_with_filters()
    {
        // Create various types of images
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);
        
        GalleryImage::factory()->count(3)->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $this->fest->id,
        ]);
        
        GalleryImage::factory()->count(1)->create([
            'imageable_type' => null,
            'imageable_id' => null,
        ]);

        // Test filter by events
        $eventImages = $this->galleryService->getAllGalleryImages(['type' => 'events']);
        $this->assertEquals(2, $eventImages->total());

        // Test filter by fests
        $festImages = $this->galleryService->getAllGalleryImages(['type' => 'fests']);
        $this->assertEquals(3, $festImages->total());

        // Test filter by general
        $generalImages = $this->galleryService->getAllGalleryImages(['type' => 'general']);
        $this->assertEquals(1, $generalImages->total());

        // Test filter by specific event
        $specificEventImages = $this->galleryService->getAllGalleryImages(['event_id' => $this->event->id]);
        $this->assertEquals(2, $specificEventImages->total());

        // Test no filter (all images)
        $allImages = $this->galleryService->getAllGalleryImages();
        $this->assertEquals(6, $allImages->total());
    }

    public function test_update_image()
    {
        $image = GalleryImage::factory()->create([
            'caption' => 'Original caption',
            'alt_text' => 'Original alt text',
        ]);

        $updateData = [
            'caption' => 'Updated caption',
            'alt_text' => 'Updated alt text',
        ];

        $result = $this->galleryService->updateImage($image, $updateData);

        $this->assertTrue($result);
        $image->refresh();
        $this->assertEquals('Updated caption', $image->caption);
        $this->assertEquals('Updated alt text', $image->alt_text);
    }

    public function test_delete_image_as_super_admin()
    {
        Storage::fake('public');
        
        $image = GalleryImage::factory()->create([
            'image_path' => 'test/image.jpg',
            'thumbnail_path' => 'test/thumb_image.jpg',
            'uploaded_by' => User::factory()->create()->id,
        ]);
        
        // Create fake files
        Storage::disk('public')->put($image->image_path, 'fake content');
        Storage::disk('public')->put($image->thumbnail_path, 'fake thumb content');

        $result = $this->galleryService->deleteImage($image, $this->user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
        Storage::disk('public')->assertMissing($image->thumbnail_path);
    }

    public function test_delete_image_as_uploader()
    {
        $uploader = User::factory()->create(['role' => 'member']);
        
        $image = GalleryImage::factory()->create([
            'uploaded_by' => $uploader->id,
        ]);

        $result = $this->galleryService->deleteImage($image, $uploader);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }

    public function test_delete_image_fails_without_permission()
    {
        $otherUser = User::factory()->create(['role' => 'member']);
        
        $image = GalleryImage::factory()->create([
            'uploaded_by' => $this->user->id,
        ]);

        $result = $this->galleryService->deleteImage($image, $otherUser);

        $this->assertFalse($result);
        $this->assertDatabaseHas('gallery_images', ['id' => $image->id]);
    }

    public function test_bulk_delete_images()
    {
        $images = GalleryImage::factory()->count(3)->create([
            'uploaded_by' => $this->user->id,
        ]);

        $imageIds = $images->pluck('id')->toArray();
        $results = $this->galleryService->bulkDeleteImages($imageIds, $this->user);

        $this->assertEquals(3, $results['success']);
        $this->assertEquals(0, $results['failed']);
        
        foreach ($images as $image) {
            $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
        }
    }

    public function test_reassociate_image()
    {
        $image = GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);

        $result = $this->galleryService->reassociateImage($image, $this->fest);

        $this->assertTrue($result);
        $image->refresh();
        $this->assertEquals($this->fest->id, $image->imageable_id);
        $this->assertEquals(Fest::class, $image->imageable_type);
    }

    public function test_reassociate_image_to_general_gallery()
    {
        $image = GalleryImage::factory()->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);

        $result = $this->galleryService->reassociateImage($image, null);

        $this->assertTrue($result);
        $image->refresh();
        $this->assertNull($image->imageable_id);
        $this->assertNull($image->imageable_type);
    }

    public function test_get_gallery_statistics()
    {
        // Create various types of images
        GalleryImage::factory()->count(2)->create([
            'imageable_type' => Event::class,
            'imageable_id' => $this->event->id,
        ]);
        
        GalleryImage::factory()->count(3)->create([
            'imageable_type' => Fest::class,
            'imageable_id' => $this->fest->id,
        ]);
        
        GalleryImage::factory()->count(1)->create([
            'imageable_type' => null,
            'imageable_id' => null,
        ]);

        $stats = $this->galleryService->getGalleryStatistics();

        $this->assertEquals(6, $stats['total_images']);
        $this->assertEquals(2, $stats['event_images']);
        $this->assertEquals(3, $stats['fest_images']);
        $this->assertEquals(1, $stats['general_images']);
        $this->assertArrayHasKey('total_size', $stats);
    }
}