<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImageUploadService
{
    /**
     * Allowed MIME types and max file size (5MB).
     */
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    protected int $maxSize = 5242880; // 5MB in bytes

    /**
     * Upload an image and attach it to a model.
     */
    public function upload(UploadedFile $file, Model $model, bool $isPrimary = false): Image
    {
        $this->validateFile($file);

        $modelType = class_basename($model);
        $directory = 'images/' . strtolower($modelType) . '/' . $model->id;

        $path = $file->store($directory, 'public');

        // If this is set as primary, unset other primaries
        if ($isPrimary) {
            $model->images()->update(['is_primary' => false]);
        }

        // Set as primary if it's the first image
        $existingCount = $model->images()->count();
        if ($existingCount === 0) {
            $isPrimary = true;
        }

        return Image::create([
            'imageable_type' => get_class($model),
            'imageable_id'   => $model->id,
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
            'file_size'       => $file->getSize(),
            'is_primary'      => $isPrimary,
            'sort_order'      => $existingCount,
        ]);
    }

    /**
     * Upload multiple images for a model.
     */
    public function uploadMultiple(array $files, Model $model): array
    {
        $images = [];
        foreach ($files as $index => $file) {
            $images[] = $this->upload($file, $model, $index === 0 && $model->images()->count() === 0);
        }
        return $images;
    }

    /**
     * Delete an image and its file.
     */
    public function delete(Image $image): bool
    {
        Storage::disk('public')->delete($image->file_path);

        $wasPrimary = $image->is_primary;
        $model = $image->imageable;
        $image->delete();

        // If we deleted the primary, promote the next one
        if ($wasPrimary && $model) {
            $next = $model->images()->orderBy('sort_order')->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return true;
    }

    /**
     * Set an image as primary for its model.
     */
    public function setPrimary(Image $image): void
    {
        $image->imageable->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    /**
     * Validate file before upload.
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            throw ValidationException::withMessages([
                'image' => ['File must be an image (jpeg, png, webp, gif).'],
            ]);
        }

        if ($file->getSize() > $this->maxSize) {
            throw ValidationException::withMessages([
                'image' => ['Image must be less than 5MB.'],
            ]);
        }
    }
}
