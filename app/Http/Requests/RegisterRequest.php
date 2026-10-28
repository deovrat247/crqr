<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'username' => strtolower(ltrim(trim((string) $this->input('username')), '@')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::notIn(config('crqr.reserved')),
                Rule::unique('users', 'username'),
            ],
            'email' => ['required', 'string', 'email', 'max:254', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Use lowercase letters, numbers and underscores only.',
            'username.not_in' => 'That username is reserved. Try another one.',
            'username.unique' => 'That username is taken. Try another one.',
            'email.unique' => 'An account with this email already exists. Log in instead.',
        ];
    }
}
