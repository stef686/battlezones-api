<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;

class ThumbnailService
{
    /**
     * Generate a thumbnail from an uploaded image and store it on the public disk.
     */
    public function generate(UploadedFile $file, int $userId): string
    {
        $extension = $file->getClientOriginalExtension();
        $path = "photos/{$userId}/thumbs/".Str::uuid().".{$extension}";

        /** @var ImageInterface $image */
        $image = Image::read($file);
        $image->scaleDown(width: 400);

        Storage::disk('public')->put($path, $image->encodeByExtension($extension));

        return $path;
    }
}
