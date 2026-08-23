<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('photo'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, array{description: string, example?: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The photo title.',
                'example' => 'My painted army',
            ],
            'photo' => [
                'description' => 'A replacement photo file (jpg, jpeg, png, or webp, max 10MB).',
            ],
            'description' => [
                'description' => 'An updated description of the photo.',
                'example' => 'My fully painted Space Marines army.',
            ],
        ];
    }
}
