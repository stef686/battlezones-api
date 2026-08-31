<?php

namespace App\Http\Requests\Events;

use App\Models\EventAttendee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * What may be uploaded as a team's Avatar, and what may not.
 *
 * The type list is the Banner's explicit allowlist, and for the same reason:
 * SVG is a document that can carry script, so refusing it is a security
 * boundary rather than a formatting preference. Do not widen it.
 *
 * Uploads under 128x128 are refused rather than upscaled — a badge blown up
 * to fill a list row is worse than the placeholder it replaced — but any
 * aspect ratio is accepted, because the crop is to the centre of a square and
 * a photograph of a painted model is rarely square to begin with.
 */
#[BodyParam('avatar', 'file', 'A square-ish image, at least 128x128, at most 8MB. JPEG, PNG or WebP.', required: true)]
class StoreAttendeeAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->attendee()) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'mimes:jpeg,png,webp',
                'max:8192',
                Rule::dimensions()->minWidth(128)->minHeight(128),
            ],
        ];
    }

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function attendee(): EventAttendee
    {
        $attendee = $this->route('attendee');

        return $attendee instanceof EventAttendee ? $attendee : new EventAttendee();
    }
}
