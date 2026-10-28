<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesUrls;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    use NormalizesUrls;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('link'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => $this->normalizeUrl($this->input('url')),
            'title' => trim((string) $this->input('title')) ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'url' => $this->destinationRules(),
            'title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'Enter a valid web address, like https://example.com.',
        ];
    }
}
