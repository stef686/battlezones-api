<?php

namespace App\Http\Requests\Events;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('email', 'string', 'The email of the claimed account to appoint.', required: true, example: 'organiser@example.com')]
class StoreEventOrganiserRequest extends FormRequest
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
            'email' => ['required', 'email'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->organiser();

                if (! $user instanceof User) {
                    $validator->errors()->add('email', 'No account exists for this email address.');

                    return;
                }

                // Appointing an unclaimed account would let a forwarded invite
                // email grant the power to edit results.
                if (! $user->isClaimed()) {
                    $validator->errors()->add(
                        'email',
                        'This person needs to finish setting up their account before they can be made an organiser.'
                    );
                }
            },
        ];
    }

    public function organiser(): ?User
    {
        return User::where('email', $this->string('email')->toString())->first();
    }
}
