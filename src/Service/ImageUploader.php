<?php

namespace App\Service;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ImageUploader
{
    private ImageManager $imageManager;
    private SluggerInterface $slugger;
    private string $uploadDirectory;
    private array $allowedMimeTypes;
    private int $maxFileSize;
    private int $maxWidth;
    private int $maxHeight;
    private int $quality;

    public function __construct(
        SluggerInterface $slugger,
        #[Autowire('%image_directory%')]
        string $uploadDirectory,
        #[Autowire(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])]
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        #[Autowire(2097152)] // 2MB (2 * 1024 * 1024)
        int $maxFileSize = 2097152,
        #[Autowire(1920)]
        int $maxWidth = 1920,
        #[Autowire(1080)]
        int $maxHeight = 1080,
        #[Autowire(85)]
        int $quality = 85
    ) {
        $this->imageManager = new ImageManager(new Driver());
        $this->slugger = $slugger;
        $this->uploadDirectory = $uploadDirectory;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->quality = $quality;

        // Ensure upload directory exists
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0755, true);
        }
    }

    /**
     * Upload and process a single image
     */
    public function upload(UploadedFile $file, ?string $prefix = null): string
    {
        $this->validateFile($file);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $this->getOptimalExtension($file);

        $fileName = ($prefix ? $prefix . '_' : '') . $safeFilename . '-' . uniqid() . '.' . $extension;

        try {
            // Create image instance
            $image = $this->imageManager->read($file->getPathname());

            // Apply optimizations
            $this->optimizeImage($image);

            // Save optimized image
            $image->save($this->uploadDirectory . '/' . $fileName, $this->quality);

            return $fileName;

        } catch (\Exception $e) {
            throw new FileException('Failed to process image: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple images
     */
    public function uploadMultiple(array $files, ?string $prefix = null): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $uploadedFiles[] = $this->upload($file, $prefix);
                } catch (FileException $e) {
                    // Log error but continue with other files
                    error_log('Image upload failed: ' . $e->getMessage());
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Delete an image file
     */
    public function delete(string $filename): bool
    {
        $filePath = $this->uploadDirectory . '/' . $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Delete multiple image files
     */
    public function deleteMultiple(array $filenames): array
    {
        $results = [];

        foreach ($filenames as $filename) {
            $results[$filename] = $this->delete($filename);
        }

        return $results;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $filename): ?array
    {
        $filePath = $this->uploadDirectory . '/' . $filename;

        if (!file_exists($filePath)) {
            return null;
        }

        try {
            $image = $this->imageManager->read($filePath);
            return [
                'width' => $image->width(),
                'height' => $image->height()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(string $filename, int $width = 300, int $height = 200): ?string
    {
        $filePath = $this->uploadDirectory . '/' . $filename;

        if (!file_exists($filePath)) {
            return null;
        }

        $pathInfo = pathinfo($filename);
        $thumbnailName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $thumbnailPath = $this->uploadDirectory . '/' . $thumbnailName;

        try {
            $image = $this->imageManager->read($filePath);
            $image->cover($width, $height);
            $image->save($thumbnailPath, $this->quality);

            return $thumbnailName;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new FileException(sprintf(
                'File size (%s) exceeds maximum allowed size (%s)',
                $this->formatFileSize($file->getSize()),
                $this->formatFileSize($this->maxFileSize)
            ));
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new FileException(sprintf(
                'File type "%s" is not allowed. Allowed types: %s',
                $file->getMimeType(),
                implode(', ', $this->allowedMimeTypes)
            ));
        }

        // Verify it's actually an image
        if (!getimagesize($file->getPathname())) {
            throw new FileException('File is not a valid image');
        }
    }

    /**
     * Optimize image (resize, compress)
     */
    private function optimizeImage($image): void
    {
        // Resize if too large
        if ($image->width() > $this->maxWidth || $image->height() > $this->maxHeight) {
            $image->scale($this->maxWidth, $this->maxHeight);
        }

        // Auto-orient based on EXIF data
        $image->orient();
    }

    /**
     * Get optimal file extension based on image type
     */
    private function getOptimalExtension(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        return match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => $file->guessExtension() ?? 'jpg'
        };
    }

    /**
     * Format file size for human reading
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }

    /**
     * Get upload directory path
     */
    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }

    /**
     * Get allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Get max file size
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Get max file size formatted
     */
    public function getMaxFileSizeFormatted(): string
    {
        return $this->formatFileSize($this->maxFileSize);
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $filename): bool
    {
        return file_exists($this->uploadDirectory . '/' . $filename);
    }

    /**
     * Get file path
     */
    public function getFilePath(string $filename): string
    {
        return $this->uploadDirectory . '/' . $filename;
    }

    /**
     * Get web path for file
     */
    public function getWebPath(string $filename): string
    {
        return '/uploads/image/' . $filename;
    }
}
