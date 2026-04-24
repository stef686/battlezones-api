<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The photo title.',
                'example' => 'My painted army',
            ],
            'photo' => [
                'description' => 'The photo file (jpg, jpeg, png, or webp, max 10MB).',
                'example' => 'photo.jpg',
            ],
            'description' => [
                'description' => 'An optional description of the photo.',
                'example' => 'My fully painted Space Marines army.',
            ],
        ];
    }
}
