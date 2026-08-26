<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * What may be uploaded as a Banner, and what may not.
 *
 * The type list is an explicit allowlist rather than Laravel's `image` rule.
 * SVG is a document that can carry script and Banners are served from the same
 * public disk as everything else, so refusing it is a security boundary, not a
 * formatting preference — do not widen this. Animated GIF is refused too: it
 * cannot survive the crop meaningfully, and motion behind the Event's name
 * fights reading it.
 *
 * Small uploads are rejected rather than upscaled, which does refuse a square
 * club logo. Accepting one would need a second layout mode in the header, and
 * the header deliberately has one. See ADR 0003.
 */
#[BodyParam('banner', 'file', 'A wide image, at least 1200x400, at most 8MB. JPEG, PNG or WebP.', required: true)]
class StoreEventBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organise', $this->event()) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'banner' => [
                'required',
                'file',
                'mimes:jpeg,png,webp',
                'max:8192',
                Rule::dimensions()->minWidth(1200)->minHeight(400),
            ],
        ];
    }

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function event(): Event
    {
        $event = $this->route('event');

        return $event instanceof Event ? $event : new Event();
    }
}
