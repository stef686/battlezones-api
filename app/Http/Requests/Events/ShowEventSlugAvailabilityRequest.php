<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\Events\Concerns\ValidatesEventSlug;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A slug the wizard is about to propose, held to the shape the create holds
 * it to. A malformed or reserved slug is refused here with the create's own
 * words rather than answered "free", since the create would refuse it anyway.
 */
class ShowEventSlugAvailabilityRequest extends FormRequest
{
    use ValidatesEventSlug;

    /**
     * Only someone who could create an Event at the slug may ask about it.
     * The answer says whether some Event, possibly a Draft, exists there, so
     * an account that could never use it gets no answer at all.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => $this->slugShapeRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->slugMessages();
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'slug' => [
                'description' => 'The slug to check: lowercase letters and digits, with single hyphens between words, 3 to 60 characters, and not a reserved word.',
                'example' => 'croydon-doubles',
            ],
        ];
    }
}
