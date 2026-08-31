<?php

namespace App\Services;

use App\Models\EventAttendee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Str;

/**
 * A team's Avatar: one square, normalised on the way in, original discarded.
 *
 * The same bargain as a Banner (see docs/adr/0003) for the same reason — the
 * app never has to reason about what shape an upload was — but a single
 * variant rather than a pair, because an Avatar is only ever drawn small: a
 * row in the Attendees list, a row in the Standings, a side of a pairing.
 * 256px covers those at twice the density of the largest of them.
 *
 * Cropped from the centre rather than the top, which is the opposite of a
 * Banner: nothing is overlaid on an Avatar, and the subject of a square badge
 * or a photographed model is in the middle of it.
 */
class AttendeeAvatarService
{
    private const SIZE = 256;

    private const QUALITY = 82;

    /**
     * Replace whatever this team had with a normalised square, and record it.
     */
    public function replace(EventAttendee $attendee, UploadedFile $file): void
    {
        $this->delete($attendee);

        $image = Image::fromUpload($file)
            ->cover(self::SIZE, self::SIZE)
            ->quality(self::QUALITY)
            ->toWebp();

        $path = "avatars/{$attendee->getKey()}/".Str::uuid().'.webp';

        UploadStorage::disk()->put($path, $image->toBytes());

        $attendee->forceFill(['avatar_path' => $path])->save();
    }

    /**
     * Take the Avatar off the team and off the disk.
     */
    public function delete(EventAttendee $attendee): void
    {
        if ($attendee->avatar_path !== null) {
            UploadStorage::disk()->delete($attendee->avatar_path);
        }

        $attendee->forceFill(['avatar_path' => null])->save();
    }
}
