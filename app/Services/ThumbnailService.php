<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThumbnailService
{
    /**
     * Generate a thumbnail from an uploaded image and store it on the public disk.
     */
    public function generate(UploadedFile $file, int $userId): string
    {
        $extension = $file->getClientOriginalExtension();
        $path = "photos/{$userId}/thumbs/".Str::uuid().".{$extension}";

        $thumbnail = Image::fromUpload($file)->scale(width: 400);

        Storage::disk('public')->put($path, $thumbnail->toBytes());

        return $path;
    }
}
