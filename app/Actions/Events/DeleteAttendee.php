<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use App\Services\UploadStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Remove an Attendee that has never been paired, as if it had never entered.
 *
 * The no-show before Round 1, a duplicate entry, a refund. The line is the
 * first Game, Draft Rounds included, rather than a date: once an Attendee has
 * been paired its opponents' Games depend on it, and leaving part-way is a
 * Drop, which keeps what was played.
 *
 * Memberships, custom field responses, Invites and Poll ballots naming the
 * Attendee go with it by cascade; User accounts, claimed or not, are left
 * alone. The Avatar file is removed only once the delete has committed, so a
 * rolled-back delete never leaves a team pointing at a missing file.
 */
class DeleteAttendee
{
    /**
     * @throws ValidationException
     */
    public function execute(EventAttendee $attendee): void
    {
        DB::transaction(function () use ($attendee): void {
            // Locked against GenerateRoundPairings, which reads its field
            // under a shared lock in the same transaction as it writes the
            // Games: whichever goes second waits for the other to commit, so
            // a Round can never be paired with an Attendee deleted under it.
            $refused = EventAttendee::query()
                ->whereKey($attendee->getKey())
                ->lockForUpdate()
                ->whereHas('games')
                ->exists();

            if ($refused) {
                throw ValidationException::withMessages([
                    'attendee' => 'This team has been paired, so it cannot be deleted. Drop it instead.',
                ]);
            }

            $avatar = $attendee->avatar_path;

            $attendee->delete();

            if ($avatar !== null) {
                DB::afterCommit(fn () => UploadStorage::disk()->delete($avatar));
            }
        });
    }
}
