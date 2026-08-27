<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Str;

/**
 * An Event's Banner: normalised on the way in, original discarded.
 *
 * Borrows the shape of PhotoStorageService — delete-then-store on replace,
 * UUID filenames, the uploads disk — but not its code: a Banner is not a Photo.
 * It is not in the Gallery, not attributed to a Player and not reactable.
 *
 * See docs/adr/0003-banners-are-normalised-on-upload.md. The crop is biased
 * toward the top because the Event's name and dates are overlaid at the
 * bottom of the header, so a centred crop would put a face or a logo exactly
 * where the type sits.
 */
class EventBannerService
{
    private const LARGE = [1600, 534];

    private const SMALL = [800, 267];

    private const QUALITY = 82;

    /**
     * Replace whatever this Event had with a normalised pair, and record them.
     */
    public function replace(Event $event, UploadedFile $file): void
    {
        $this->delete($event);

        $directory = "banners/{$event->getKey()}";

        $event->forceFill([
            'banner_path' => $this->write($file, $directory, self::LARGE),
            'banner_small_path' => $this->write($file, $directory, self::SMALL),
        ])->save();
    }

    /**
     * Take the Banner off the Event and off the disk.
     */
    public function delete(Event $event): void
    {
        UploadStorage::disk()->delete(
            array_filter([$event->banner_path, $event->banner_small_path]),
        );

        $event->forceFill(['banner_path' => null, 'banner_small_path' => null])->save();
    }

    /**
     * Scale to cover the target, then crop to it from the top.
     *
     * Cropped by hand rather than with `cover()`, which centres: the Event's
     * name and dates sit at the bottom of the header, so a centred crop puts a
     * face or a logo exactly where the type goes. Horizontally it is centred,
     * because nothing is overlaid at either edge.
     *
     * @param  array{int, int}  $size
     */
    private function write(UploadedFile $file, string $directory, array $size): string
    {
        [$width, $height] = $size;
        [$sourceWidth, $sourceHeight] = $this->dimensionsOf($file);

        $scale = max($width / $sourceWidth, $height / $sourceHeight);
        $scaledWidth = (int) ceil($sourceWidth * $scale);
        $scaledHeight = (int) ceil($sourceHeight * $scale);

        $image = Image::fromUpload($file)
            ->resize($scaledWidth, $scaledHeight)
            ->crop($width, $height, intdiv(max(0, $scaledWidth - $width), 2), 0)
            ->quality(self::QUALITY)
            ->toWebp();

        $path = "{$directory}/".Str::uuid().'.webp';

        UploadStorage::disk()->put($path, $image->toBytes());

        return $path;
    }

    /**
     * @return array{int, int}
     */
    private function dimensionsOf(UploadedFile $file): array
    {
        $dimensions = getimagesize((string) $file->getRealPath());

        // Validation has already refused anything PHP cannot read as an image.
        return [(int) $dimensions[0], (int) $dimensions[1]];
    }
}
