<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileOptimizationService
{
    const MAX_IMAGE_SIZE = 2048; // Max width/height in pixels
    const JPEG_QUALITY = 85; // JPEG compression quality
    const WEBP_QUALITY = 80; // WebP compression quality

    /**
     * Optimize and store an uploaded image
     */
    public function optimizeAndStoreImage(UploadedFile $file, string $directory = 'images'): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->generateOptimizedFilename($originalName);
        
        // Create optimized versions
        $results = [
            'original' => null,
            'webp' => null,
            'thumbnails' => [],
            'metadata' => [
                'original_name' => $originalName,
                'original_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]
        ];

        try {
            // Store original optimized version
            $optimizedPath = $this->createOptimizedImage($file, $directory, $filename, $extension);
            $results['original'] = $optimizedPath;

            // Create WebP version for modern browsers
            if ($this->supportsWebP()) {
                $webpPath = $this->createWebPVersion($file, $directory, $filename);
                $results['webp'] = $webpPath;
            }

            // Create responsive thumbnails
            $results['thumbnails'] = $this->createResponsiveThumbnails($file, $directory, $filename);

            return $results;

        } catch (\Exception $e) {
            \Log::error('Image optimization failed: ' . $e->getMessage());
            
            // Fallback: store original file
            $fallbackPath = $file->store($directory, 'public');
            return [
                'original' => $fallbackPath,
                'webp' => null,
                'thumbnails' => [],
                'metadata' => $results['metadata']
            ];
        }
    }

    /**
     * Create optimized image
     */
    private function createOptimizedImage(UploadedFile $file, string $directory, string $filename, string $extension): string
    {
        if (!class_exists('Intervention\Image\Facades\Image')) {
            // Fallback if Intervention Image is not available
            return $file->storeAs($directory, $filename . '.' . $extension, 'public');
        }

        $image = \Intervention\Image\Facades\Image::make($file->getPathname());
        
        // Resize if too large
        if ($image->width() > self::MAX_IMAGE_SIZE || $image->height() > self::MAX_IMAGE_SIZE) {
            $image->resize(self::MAX_IMAGE_SIZE, self::MAX_IMAGE_SIZE, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Optimize based on format
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image->encode('jpg', self::JPEG_QUALITY);
                break;
            case 'png':
                // For PNG, we'll convert to JPEG if it doesn't need transparency
                if (!$this->hasTransparency($image)) {
                    $image->encode('jpg', self::JPEG_QUALITY);
                    $extension = 'jpg';
                } else {
                    $image->encode('png');
                }
                break;
            case 'gif':
                // Keep GIF as is for animations, or convert to JPEG for static
                if (!$this->isAnimatedGif($file)) {
                    $image->encode('jpg', self::JPEG_QUALITY);
                    $extension = 'jpg';
                }
                break;
        }

        $optimizedPath = $directory . '/' . $filename . '.' . $extension;
        $fullPath = storage_path('app/public/' . $optimizedPath);
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $image->save($fullPath);
        
        return $optimizedPath;
    }

    /**
     * Create WebP version for modern browsers
     */
    private function createWebPVersion(UploadedFile $file, string $directory, string $filename): ?string
    {
        if (!class_exists('Intervention\Image\Facades\Image')) {
            return null;
        }

        try {
            $image = \Intervention\Image\Facades\Image::make($file->getPathname());
            
            // Resize if too large
            if ($image->width() > self::MAX_IMAGE_SIZE || $image->height() > self::MAX_IMAGE_SIZE) {
                $image->resize(self::MAX_IMAGE_SIZE, self::MAX_IMAGE_SIZE, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $webpPath = $directory . '/' . $filename . '.webp';
            $fullPath = storage_path('app/public/' . $webpPath);
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $image->encode('webp', self::WEBP_QUALITY)->save($fullPath);
            
            return $webpPath;

        } catch (\Exception $e) {
            \Log::warning('WebP creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create responsive thumbnails
     */
    private function createResponsiveThumbnails(UploadedFile $file, string $directory, string $filename): array
    {
        if (!class_exists('Intervention\Image\Facades\Image')) {
            return [];
        }

        $thumbnails = [];
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
            'xlarge' => [1200, 1200]
        ];

        $thumbnailDir = $directory . '/thumbnails';

        foreach ($sizes as $sizeName => $dimensions) {
            try {
                $image = \Intervention\Image\Facades\Image::make($file->getPathname());
                
                $image->resize($dimensions[0], $dimensions[1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Create both JPEG and WebP versions
                $jpegPath = $thumbnailDir . '/' . $sizeName . '_' . $filename . '.jpg';
                $webpPath = $thumbnailDir . '/' . $sizeName . '_' . $filename . '.webp';
                
                $jpegFullPath = storage_path('app/public/' . $jpegPath);
                $webpFullPath = storage_path('app/public/' . $webpPath);
                
                // Ensure directory exists
                $dir = dirname($jpegFullPath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Save JPEG version
                $image->encode('jpg', self::JPEG_QUALITY)->save($jpegFullPath);
                $thumbnails[$sizeName] = ['jpeg' => $jpegPath];

                // Save WebP version if supported
                if ($this->supportsWebP()) {
                    $image->encode('webp', self::WEBP_QUALITY)->save($webpFullPath);
                    $thumbnails[$sizeName]['webp'] = $webpPath;
                }

            } catch (\Exception $e) {
                \Log::warning("Thumbnail creation failed for size {$sizeName}: " . $e->getMessage());
            }
        }

        return $thumbnails;
    }

    /**
     * Generate optimized filename
     */
    private function generateOptimizedFilename(string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = Str::slug($name);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(6);
        
        return $timestamp . '_' . $name . '_' . $random;
    }

    /**
     * Check if system supports WebP
     */
    private function supportsWebP(): bool
    {
        return function_exists('imagewebp') && class_exists('Intervention\Image\Facades\Image');
    }

    /**
     * Check if image has transparency
     */
    private function hasTransparency($image): bool
    {
        // This is a simplified check - in practice, you might want more sophisticated detection
        return false;
    }

    /**
     * Check if GIF is animated
     */
    private function isAnimatedGif(UploadedFile $file): bool
    {
        if ($file->getMimeType() !== 'image/gif') {
            return false;
        }

        $content = file_get_contents($file->getPathname());
        return strpos($content, "\x00\x21\xF9\x04") !== false;
    }

    /**
     * Clean up old files
     */
    public function cleanupOldFiles(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Clean up thumbnails
        $pathInfo = pathinfo($path);
        $thumbnailPattern = $pathInfo['dirname'] . '/thumbnails/*' . $pathInfo['filename'] . '*';
        
        $thumbnailFiles = Storage::disk('public')->files($pathInfo['dirname'] . '/thumbnails');
        foreach ($thumbnailFiles as $thumbnailFile) {
            if (strpos(basename($thumbnailFile), $pathInfo['filename']) !== false) {
                Storage::disk('public')->delete($thumbnailFile);
            }
        }
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanReadableSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Calculate storage savings
     */
    public function calculateStorageSavings(int $originalSize, string $optimizedPath): array
    {
        if (!Storage::disk('public')->exists($optimizedPath)) {
            return ['savings' => 0, 'percentage' => 0];
        }

        $optimizedSize = Storage::disk('public')->size($optimizedPath);
        $savings = $originalSize - $optimizedSize;
        $percentage = $originalSize > 0 ? ($savings / $originalSize) * 100 : 0;

        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'savings' => $savings,
            'percentage' => round($percentage, 2)
        ];
    }
}