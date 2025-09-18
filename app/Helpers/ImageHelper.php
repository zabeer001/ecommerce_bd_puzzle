<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * Uploads an image to the specified disk.
     *
     * @param UploadedFile $image
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public static function uploadImage(UploadedFile $image, string $path = 'uploads', string $disk = 'public'): ?string
    {
        try {
            return $image->store($path, $disk);
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage(), [
                'error' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Deletes a file from the specified disk.
     *
     * @param string|null $filePath
     * @param string $disk
     * @return bool
     */
    public static function deleteImage(?string $filePath, string $disk = 'public'): bool
    {
        if ($filePath && Storage::disk($disk)->exists($filePath)) {
            try {
                return Storage::disk($disk)->delete($filePath);
            } catch (\Exception $e) {
                Log::error('Error deleting image: ' . $e->getMessage(), [
                    'image_path' => $filePath,
                    'error' => $e->getTraceAsString(),
                ]);
                return false;
            }
        }
        return false;
    }

    /**
     * Updates an existing image by deleting the old one and uploading a new one.
     *
     * @param UploadedFile $newImage The new image file to upload.
     * @param string|null $oldImagePath The path to the old image to delete.
     * @param string $disk The filesystem disk to use.
     * @return string|null The path to the new image.
     */
    public static function updateImage(UploadedFile $newImage, ?string $oldImagePath = null, string $disk = 'public'): ?string
    {
        // Delete the old image if it exists and a new image is provided
        if ($oldImagePath) {
            self::deleteImage($oldImagePath, $disk);
        }

        // Upload the new image
        return self::uploadImage($newImage, 'uploads', $disk);
    }
}