<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Fest;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Intervention\Image\Facades\Image;

class GalleryService
{
    /**
     * Upload multiple images
     */
    public function uploadImages(array $files, $imageable, User $uploader): Collection
    {
        $uploadedImages = collect();
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $image = $this->uploadSingleImage($file, $imageable, $uploader);
                if ($image) {
                    $uploadedImages->push($image);
                }
            }
        }
        
        return $uploadedImages;
    }
    
    /**
     * Upload a single image
     */
    public function uploadSingleImage(UploadedFile $file, $imageable, User $uploader, array $options = []): ?GalleryImage
    {
        try {
            // Validate file
            $this->validateImageFile($file);
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);
            
            // Determine storage path
            $storagePath = $this->getStoragePath($imageable);
            
            // Store original image
            $path = $file->storeAs($storagePath, $filename, 'public');
            
            // Create thumbnail if needed
            $thumbnailPath = null;
            try {
                $thumbnailPath = $this->createThumbnail($file, $storagePath, $filename);
            } catch (\Exception $e) {
                \Log::warning('Failed to create thumbnail: ' . $e->getMessage());
            }
            
            // Create gallery image record
            $galleryImage = new GalleryImage([
                'image_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'caption' => $options['caption'] ?? null,
                'alt_text' => $options['alt_text'] ?? $this->generateAltText($imageable),
                'uploaded_by' => $uploader->id,
            ]);
            
            // Associate with imageable if provided
            if ($imageable) {
                $galleryImage->imageable()->associate($imageable);
            }
            
            $galleryImage->save();
            
            return $galleryImage;
            
        } catch (\Exception $e) {
            \Log::error('Failed to upload image: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get gallery images for an event
     */
    public function getEventGallery(Event $event): Collection
    {
        return GalleryImage::where('imageable_type', Event::class)
            ->where('imageable_id', $event->id)
            ->with('uploader:id,name')
            ->select('id', 'imageable_type', 'imageable_id', 'image_path', 'thumbnail_path', 'caption', 'alt_text', 'uploaded_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get gallery images for a fest
     */
    public function getFestGallery(Fest $fest): Collection
    {
        return GalleryImage::where('imageable_type', Fest::class)
            ->where('imageable_id', $fest->id)
            ->with('uploader:id,name')
            ->select('id', 'imageable_type', 'imageable_id', 'image_path', 'thumbnail_path', 'caption', 'alt_text', 'uploaded_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get general gallery images (not associated with any event/fest)
     */
    public function getGeneralGallery(): Collection
    {
        return GalleryImage::whereNull('imageable_type')
            ->whereNull('imageable_id')
            ->with('uploader:id,name')
            ->select('id', 'imageable_type', 'imageable_id', 'image_path', 'thumbnail_path', 'caption', 'alt_text', 'uploaded_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get all gallery images with optional filtering
     */
    public function getAllGalleryImages(array $filters = []): LengthAwarePaginator
    {
        $query = GalleryImage::with([
            'uploader:id,name',
            'imageable' => function ($morphTo) {
                $morphTo->morphWith([
                    Event::class => ['id', 'title', 'event_date'],
                    Fest::class => ['id', 'title', 'start_date', 'end_date']
                ]);
            }
        ])->select('id', 'imageable_type', 'imageable_id', 'image_path', 'thumbnail_path', 'caption', 'alt_text', 'uploaded_by', 'created_at');
        
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            switch ($filters['type']) {
                case 'events':
                    $query->where('imageable_type', Event::class);
                    break;
                case 'fests':
                    $query->where('imageable_type', Fest::class);
                    break;
                case 'general':
                    $query->whereNull('imageable_type');
                    break;
            }
        }
        
        if (isset($filters['event_id'])) {
            $query->where('imageable_type', Event::class)
                  ->where('imageable_id', $filters['event_id']);
        }
        
        if (isset($filters['fest_id'])) {
            $query->where('imageable_type', Fest::class)
                  ->where('imageable_id', $filters['fest_id']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
    
    /**
     * Update image details
     */
    public function updateImage(GalleryImage $image, array $data): bool
    {
        try {
            $image->update([
                'caption' => $data['caption'] ?? $image->caption,
                'alt_text' => $data['alt_text'] ?? $image->alt_text,
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update image: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an image
     */
    public function deleteImage(GalleryImage $image, User $user): bool
    {
        try {
            // Check if user has permission to delete
            if (!$this->canDeleteImage($image, $user)) {
                throw new \Exception('User does not have permission to delete this image');
            }
            
            // Delete files from storage
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            
            if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
            
            // Delete database record
            $image->delete();
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Failed to delete image: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bulk delete images
     */
    public function bulkDeleteImages(array $imageIds, User $user): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        
        $images = GalleryImage::whereIn('id', $imageIds)->get();
        
        foreach ($images as $image) {
            if ($this->deleteImage($image, $user)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to delete image ID: {$image->id}";
            }
        }
        
        return $results;
    }
    
    /**
     * Associate image with a different imageable
     */
    public function reassociateImage(GalleryImage $image, $newImageable): bool
    {
        try {
            if ($newImageable) {
                $image->imageable()->associate($newImageable);
            } else {
                $image->imageable()->dissociate();
            }
            
            $image->save();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to reassociate image: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get image statistics
     */
    public function getGalleryStatistics(): array
    {
        return [
            'total_images' => GalleryImage::count(),
            'event_images' => GalleryImage::where('imageable_type', Event::class)->count(),
            'fest_images' => GalleryImage::where('imageable_type', Fest::class)->count(),
            'general_images' => GalleryImage::whereNull('imageable_type')->count(),
            'total_size' => $this->calculateTotalStorageSize(),
        ];
    }
    
    /**
     * Validate image file
     */
    private function validateImageFile(UploadedFile $file): void
    {
        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('File size exceeds 5MB limit');
        }
        
        // Check file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed');
        }
        
        // Check if file is actually an image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \Exception('File is not a valid image');
        }
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Get storage path based on imageable type
     */
    private function getStoragePath($imageable): string
    {
        if ($imageable instanceof Event) {
            return "gallery/events/{$imageable->id}";
        } elseif ($imageable instanceof Fest) {
            return "gallery/fests/{$imageable->id}";
        } else {
            return "gallery/general";
        }
    }
    
    /**
     * Create thumbnail for image
     */
    private function createThumbnail(UploadedFile $file, string $storagePath, string $filename): ?string
    {
        try {
            // For now, skip thumbnail creation to avoid dependency issues
            // This can be enhanced later with proper image processing
            return null;
            
        } catch (\Exception $e) {
            \Log::warning('Failed to create thumbnail: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate alt text based on imageable
     */
    private function generateAltText($imageable): string
    {
        if ($imageable instanceof Event) {
            return "Image from event: {$imageable->title}";
        } elseif ($imageable instanceof Fest) {
            return "Image from fest: {$imageable->title}";
        } else {
            return "Gallery image from Islamic Society";
        }
    }
    
    /**
     * Check if user can delete image
     */
    private function canDeleteImage(GalleryImage $image, User $user): bool
    {
        // Super admin can delete any image
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Content admin can delete any image
        if ($user->isContentAdmin()) {
            return true;
        }
        
        // User can delete their own uploaded images
        if ($image->uploaded_by === $user->id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate total storage size
     */
    private function calculateTotalStorageSize(): int
    {
        $totalSize = 0;
        $images = GalleryImage::all();
        
        foreach ($images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                $totalSize += Storage::disk('public')->size($image->image_path);
            }
            
            if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                $totalSize += Storage::disk('public')->size($image->thumbnail_path);
            }
        }
        
        return $totalSize;
    }
}