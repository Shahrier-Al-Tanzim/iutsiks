<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'image_path',
        'thumbnail_path',
        'caption',
        'alt_text',
        'uploaded_by',
    ];

    /**
     * Get the user who uploaded this image.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the owning imageable model (Event, Fest, or null for general gallery).
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute(): string
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return Storage::url($this->image_path);
    }

    /**
     * Get the image URL with fallback.
     */
    public function getImageUrl(): string
    {
        if (!$this->image_path) {
            return asset('images/placeholder.jpg');
        }

        return $this->image_url;
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrl(string $size = 'medium'): string
    {
        // Try to get specific size thumbnail
        $sizedThumbnailPath = $this->getSizedThumbnailPath($size);
        if ($sizedThumbnailPath && Storage::exists($sizedThumbnailPath)) {
            return Storage::url($sizedThumbnailPath);
        }

        // Fallback to default thumbnail
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            return Storage::url($this->thumbnail_path);
        }

        if (!$this->image_path) {
            return asset('images/placeholder_thumb.jpg');
        }

        // Fallback to original image if thumbnail doesn't exist
        return $this->getImageUrl();
    }

    /**
     * Get sized thumbnail path
     */
    private function getSizedThumbnailPath(string $size): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        $pathInfo = pathinfo($this->thumbnail_path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Remove existing size prefix if present
        $filename = preg_replace('/^(small|medium|large)_/', '', $filename);

        return $directory . '/' . $size . '_' . $filename . '.' . $extension;
    }

    /**
     * Get responsive image srcset for different screen sizes
     */
    public function getResponsiveSrcset(): string
    {
        $srcset = [];
        
        $sizes = ['small' => '150w', 'medium' => '300w', 'large' => '600w'];
        
        foreach ($sizes as $size => $width) {
            $url = $this->getThumbnailUrl($size);
            $srcset[] = $url . ' ' . $width;
        }
        
        return implode(', ', $srcset);
    }

    /**
     * Get the alt text with fallback.
     */
    public function getAltTextAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        if ($this->caption) {
            return $this->caption;
        }

        if ($this->imageable) {
            return $this->imageable->title ?? 'Gallery Image';
        }

        return 'Gallery Image';
    }

    /**
     * Get the display caption.
     */
    public function getDisplayCaption(): string
    {
        if ($this->caption) {
            return $this->caption;
        }

        if ($this->imageable) {
            return 'From: ' . ($this->imageable->title ?? 'Event');
        }

        return '';
    }

    /**
     * Check if image belongs to an event.
     */
    public function belongsToEvent(): bool
    {
        return $this->imageable_type === Event::class;
    }

    /**
     * Check if image belongs to a fest.
     */
    public function belongsToFest(): bool
    {
        return $this->imageable_type === Fest::class;
    }

    /**
     * Check if image is in general gallery (not associated with any event/fest).
     */
    public function isGeneralGallery(): bool
    {
        return $this->imageable_type === null && $this->imageable_id === null;
    }

    /**
     * Get the file size of the image.
     */
    public function getFileSize(): ?int
    {
        if (!$this->image_path || !Storage::exists($this->image_path)) {
            return null;
        }

        return Storage::size($this->image_path);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): string
    {
        $size = $this->getFileSize();
        
        if (!$size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Delete the image file from storage.
     */
    public function deleteImageFile(): bool
    {
        $deleted = true;
        
        // Delete main image
        if ($this->image_path && Storage::exists($this->image_path)) {
            $deleted = Storage::delete($this->image_path);
        }

        // Delete thumbnail if exists
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }

        return $deleted;
    }

    /**
     * Scope to get images for events.
     */
    public function scopeForEvents($query)
    {
        return $query->where('imageable_type', Event::class);
    }

    /**
     * Scope to get images for fests.
     */
    public function scopeForFests($query)
    {
        return $query->where('imageable_type', Fest::class);
    }

    /**
     * Scope to get general gallery images.
     */
    public function scopeGeneralGallery($query)
    {
        return $query->whereNull('imageable_type')
                    ->whereNull('imageable_id');
    }

    /**
     * Scope to get images uploaded by a specific user.
     */
    public function scopeUploadedBy($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope to get recent images.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete image file when model is deleted
        static::deleting(function ($image) {
            $image->deleteImageFile();
        });
    }
}