<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesUrls;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkRequest extends FormRequest
{
    use NormalizesUrls;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => $this->normalizeUrl($this->input('url')),
            'title' => trim((string) $this->input('title')) ?: null,
            // Codes are case-insensitive, so aliases are stored lowercase.
            'alias' => strtolower(trim((string) $this->input('alias'))) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'url' => $this->destinationRules(),
            'title' => ['nullable', 'string', 'max:120'],
            'alias' => [
                'nullable',
                'string',
                'min:3',
                'max:32',
                'regex:/^[a-z0-9][a-z0-9_-]*$/',
                Rule::notIn(config('crqr.reserved')),
                Rule::unique('links', 'code'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'Enter a valid web address, like https://example.com.',
            'alias.regex' => 'Use letters, numbers, dashes and underscores only, starting with a letter or number.',
            'alias.not_in' => 'That alias is reserved. Try another one.',
            'alias.unique' => 'That alias is already taken. Try another one.',
        ];
    }
}
