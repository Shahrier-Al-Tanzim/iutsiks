<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileSecurityService
{
    /**
     * Allowed MIME types for different file categories.
     */
    protected array $allowedMimeTypes = [
        'image' => [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/webp',
            'image/gif'
        ],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ]
    ];

    /**
     * Dangerous file extensions that should never be allowed.
     */
    protected array $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'phar',
        'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar',
        'asp', 'aspx', 'jsp', 'py', 'pl', 'rb', 'sh', 'ps1'
    ];

    /**
     * Maximum file sizes by category (in bytes).
     */
    protected array $maxFileSizes = [
        'image' => 5242880, // 5MB
        'banner' => 3145728, // 3MB
        'document' => 10485760, // 10MB
        'payment_screenshot' => 1048576, // 1MB
    ];

    /**
     * Validate and secure an uploaded file.
     */
    public function validateFile(UploadedFile $file, string $category = 'image'): array
    {
        $errors = [];

        // Check if file is valid
        if (!$file->isValid()) {
            $errors[] = 'File upload failed. Please try again.';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check file size
        if (isset($this->maxFileSizes[$category])) {
            if ($file->getSize() > $this->maxFileSizes[$category]) {
                $maxSizeMB = round($this->maxFileSizes[$category] / 1024 / 1024, 1);
                $errors[] = "File size exceeds maximum allowed size of {$maxSizeMB}MB.";
            }
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedTypes = $this->getAllowedMimeTypes($category);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'File type not allowed. Please upload a valid file.';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $this->dangerousExtensions)) {
            $errors[] = 'File extension not allowed for security reasons.';
        }

        // Validate image dimensions if it's an image
        if (str_starts_with($mimeType, 'image/')) {
            $dimensionErrors = $this->validateImageDimensions($file, $category);
            $errors = array_merge($errors, $dimensionErrors);
        }

        // Scan for malicious content
        $malwareErrors = $this->scanForMalware($file);
        $errors = array_merge($errors, $malwareErrors);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file->getSize()
        ];
    }

    /**
     * Get allowed MIME types for a category.
     */
    protected function getAllowedMimeTypes(string $category): array
    {
        return match($category) {
            'image', 'banner', 'payment_screenshot' => $this->allowedMimeTypes['image'],
            'document' => $this->allowedMimeTypes['document'],
            default => $this->allowedMimeTypes['image']
        };
    }

    /**
     * Validate image dimensions based on category.
     */
    protected function validateImageDimensions(UploadedFile $file, string $category): array
    {
        $errors = [];
        
        try {
            $imageSize = getimagesize($file->getPathname());
            if (!$imageSize) {
                $errors[] = 'Invalid image file.';
                return $errors;
            }

            [$width, $height] = $imageSize;

            $requirements = match($category) {
                'banner' => ['min_width' => 300, 'min_height' => 200, 'max_width' => 2000, 'max_height' => 1500],
                'payment_screenshot' => ['min_width' => 200, 'min_height' => 200, 'max_width' => 2000, 'max_height' => 2000],
                'image' => ['min_width' => 300, 'min_height' => 200, 'max_width' => 4000, 'max_height' => 3000],
                default => ['min_width' => 100, 'min_height' => 100, 'max_width' => 2000, 'max_height' => 2000]
            };

            if ($width < $requirements['min_width'] || $height < $requirements['min_height']) {
                $errors[] = "Image must be at least {$requirements['min_width']}x{$requirements['min_height']} pixels.";
            }

            if ($width > $requirements['max_width'] || $height > $requirements['max_height']) {
                $errors[] = "Image must not exceed {$requirements['max_width']}x{$requirements['max_height']} pixels.";
            }

        } catch (\Exception $e) {
            $errors[] = 'Unable to validate image dimensions.';
        }

        return $errors;
    }

    /**
     * Scan file for malicious content.
     */
    protected function scanForMalware(UploadedFile $file): array
    {
        $errors = [];
        
        try {
            // Read first few KB of file to check for suspicious patterns
            $handle = fopen($file->getPathname(), 'rb');
            if (!$handle) {
                $errors[] = 'Unable to scan file for security threats.';
                return $errors;
            }

            $content = fread($handle, 8192); // Read first 8KB
            fclose($handle);

            // Check for PHP code injection
            $phpPatterns = [
                '/<\?php/i',
                '/<\?=/i',
                '/<script/i',
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec/i',
                '/passthru/i',
                '/base64_decode/i'
            ];

            foreach ($phpPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $errors[] = 'File contains suspicious code and cannot be uploaded.';
                    break;
                }
            }

            // Check for embedded executables
            $executableSignatures = [
                "\x4D\x5A", // PE executable
                "\x7F\x45\x4C\x46", // ELF executable
                "\xCA\xFE\xBA\xBE", // Java class file
            ];

            foreach ($executableSignatures as $signature) {
                if (str_contains($content, $signature)) {
                    $errors[] = 'File contains executable code and cannot be uploaded.';
                    break;
                }
            }

        } catch (\Exception $e) {
            // Log error but don't block upload
            \Log::warning('File malware scan failed: ' . $e->getMessage());
        }

        return $errors;
    }

    /**
     * Securely store an uploaded file.
     */
    public function storeFile(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        // Generate secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        
        // Store file
        $path = $file->storeAs($directory, $filename, $disk);
        
        // Set secure permissions if using local disk
        if ($disk === 'public' || $disk === 'local') {
            $fullPath = Storage::disk($disk)->path($path);
            if (file_exists($fullPath)) {
                chmod($fullPath, 0644); // Read-only for group/others
            }
        }

        return $path;
    }

    /**
     * Delete a file securely.
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete file: ' . $e->getMessage(), ['path' => $path]);
            return false;
        }
    }

    /**
     * Get file info safely.
     */
    public function getFileInfo(string $path, string $disk = 'public'): ?array
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return null;
            }

            return [
                'size' => Storage::disk($disk)->size($path),
                'last_modified' => Storage::disk($disk)->lastModified($path),
                'mime_type' => Storage::disk($disk)->mimeType($path),
                'url' => Storage::disk($disk)->url($path)
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get file info: ' . $e->getMessage(), ['path' => $path]);
            return null;
        }
    }
}