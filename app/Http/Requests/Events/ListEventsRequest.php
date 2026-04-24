<?php

namespace App\Http\Requests\Events;

use App\Enums\EventStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ListEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => [
                'sometimes',
                'string',
                new Enum(EventStatus::class),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $status = EventStatus::tryFrom((string) $value);
                    if ($status !== null && ! $status->isPubliclyVisible()) {
                        $fail('The selected status is not available.');
                    }
                },
            ],
            'game_system' => ['sometimes', 'string', Rule::exists('game_systems', 'slug')],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Search events by name.',
                'example' => 'Grand Tournament',
            ],
            'status' => [
                'description' => 'Filter by event status. One of: published, active, completed.',
                'example' => 'active',
            ],
            'game_system' => [
                'description' => 'Filter by game system slug.',
                'example' => 'warhammer-40k',
            ],
        ];
    }
}
