<?php

namespace App\Http\Requests\Users;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingsRequest extends FormRequest
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
        $rules = [];

        foreach (NotificationType::cases() as $type) {
            $rules[$type->value] = ['sometimes', 'array'];
            $rules[$type->value.'.*'] = [Rule::enum(NotificationChannel::class), 'distinct'];
        }

        return $rules;
    }

    /**
     * @return array<string, array{description: string, example: array<int, string>}>
     */
    public function bodyParameters(): array
    {
        return [
            'primary_messages' => [
                'description' => 'Channels to receive primary message notifications on.',
                'example' => ['email', 'push'],
            ],
            'message_requests' => [
                'description' => 'Channels to receive message request notifications on.',
                'example' => ['push'],
            ],
            'event_messages' => [
                'description' => 'Channels to receive event message notifications on.',
                'example' => ['email'],
            ],
            'round_live' => [
                'description' => 'Channels to receive round-live notifications on.',
                'example' => ['push'],
            ],
        ];
    }
}
