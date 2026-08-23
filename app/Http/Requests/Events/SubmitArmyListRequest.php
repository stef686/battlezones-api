<?php

namespace App\Http\Requests\Events;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('army_list', 'string', 'The list as free text. No format is imposed.', required: true, example: '2000pts Ultramarines...')]
class SubmitArmyListRequest extends FormRequest
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
        // Free text by design: the platform has no business parsing every
        // army list format every game system has ever shipped.
        return [
            'army_list' => ['required', 'string'],
        ];
    }
}
