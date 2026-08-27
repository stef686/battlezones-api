<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * The one disk every user upload lives on, and the only way to link to one.
 *
 * The bucket is private, so there is no permanent URL to a Photo or a Banner:
 * every link is signed and expires. Callers do not name a disk — `local` in
 * development and the test suite, Cloudflare R2 in production — because the
 * disk is a deployment detail and a signed URL is not something to hand-roll
 * in four separate models.
 *
 * See docs/adr/0004-uploads-live-on-a-private-bucket.md.
 */
class UploadStorage
{
    /**
     * The disk uploads are written to and read back from.
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(self::name());
    }

    /**
     * A signed, expiring URL to an uploaded file.
     */
    public static function url(string $path): string
    {
        return self::disk()->temporaryUrl($path, self::expiry());
    }

    /**
     * The configured disk's name, for the rare caller that needs it — Filament
     * form components take a disk name rather than a Filesystem.
     */
    public static function name(): string
    {
        return (string) config('filesystems.uploads');
    }

    /**
     * When the URLs signed right now expire.
     *
     * Snapped forward to a fixed window rather than counted from the instant
     * of the call, so the same file signs to the same URL for every caller
     * inside the window: identical responses stay byte-identical and therefore
     * cacheable, and two reads a second apart do not disagree. A URL is always
     * good for at least one whole TTL, and at most two.
     */
    private static function expiry(): CarbonImmutable
    {
        $ttl = max(1, (int) config('filesystems.uploads_url_ttl')) * 60;

        return CarbonImmutable::createFromTimestamp(
            (intdiv(time(), $ttl) + 2) * $ttl,
        );
    }
}
