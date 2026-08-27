<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PhotoStorageService
{
    public function __construct(private ThumbnailService $thumbnailService) {}

    /**
     * Store a photo file and its thumbnail, returning both paths.
     *
     * @return array{path: string, thumbnail_path: string}
     */
    public function store(UploadedFile $file, int $userId): array
    {
        $extension = $file->getClientOriginalExtension();

        $path = $file->storeAs(
            "photos/{$userId}",
            Str::uuid().".{$extension}",
            UploadStorage::name(),
        );

        return [
            'path' => $path,
            'thumbnail_path' => $this->thumbnailService->generate($file, $userId),
        ];
    }

    /**
     * Replace an existing photo's files on disk with new ones.
     *
     * @return array{path: string, thumbnail_path: string}
     */
    public function replace(Photo $photo, UploadedFile $file, int $userId): array
    {
        $this->delete($photo);

        return $this->store($file, $userId);
    }

    /**
     * Delete a photo's files from disk.
     */
    public function delete(Photo $photo): void
    {
        UploadStorage::disk()->delete(
            array_filter([$photo->path, $photo->thumbnail_path]),
        );
    }
}
