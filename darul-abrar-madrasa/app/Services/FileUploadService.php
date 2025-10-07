<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadService
{
    /**
     * Upload avatar file
     *
     * @param UploadedFile $file
     * @param string $disk
     * @return string File path
     * @throws \Exception
     */
    public function uploadAvatar(UploadedFile $file, string $disk = 'public'): string
    {
        try {
            // Validate file is an image
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
                throw new \Exception('File must be an image (jpeg, png, jpg, gif).');
            }

            // Store file in avatars directory
            $path = $file->store('avatars', $disk);

            if (!$path) {
                throw new \Exception('Failed to upload avatar.');
            }

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Avatar upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete file from storage
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            return true; // File doesn't exist, consider it deleted
        } catch (\Exception $e) {
            // Log error but don't throw exception
            \Log::error('File deletion failed: ' . $e->getMessage(), ['path' => $path]);
            return false;
        }
    }

    /**
     * Upload document file
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return string File path
     * @throws \Exception
     */
    public function uploadDocument(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        try {
            // Store file in specified directory
            $path = $file->store($directory, $disk);

            if (!$path) {
                throw new \Exception('Failed to upload document.');
            }

            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Document upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Get file URL
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public function getFileUrl(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function fileExists(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     * @param string $disk
     * @return int
     */
    public function getFileSize(string $path, string $disk = 'public'): int
    {
        if ($this->fileExists($path, $disk)) {
            return Storage::disk($disk)->size($path);
        }
        return 0;
    }
}
