<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesUrls;
use App\Qr\QrPayload;
use App\Qr\QrStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QrRequest extends FormRequest
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
            'wifi_hidden' => $this->boolean('wifi_hidden'),
        ]);
    }

    public function rules(): array
    {
        $hex = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $phone = ['nullable', 'string', 'max:25', 'regex:/^\+?[0-9 ().-]{3,25}$/'];

        return [
            'type' => ['required', Rule::in(QrPayload::TYPES)],

            'url' => ['exclude_unless:type,url', 'required', 'url:http,https', 'max:2000'],
            'text' => ['exclude_unless:type,text', 'required', 'string', 'max:2000'],

            'wifi_ssid' => ['exclude_unless:type,wifi', 'required', 'string', 'max:32'],
            'wifi_encryption' => ['exclude_unless:type,wifi', 'required', Rule::in(QrPayload::WIFI_ENCRYPTIONS)],
            'wifi_password' => ['exclude_unless:type,wifi', 'required_unless:wifi_encryption,nopass', 'nullable', 'string', 'max:63'],
            'wifi_hidden' => ['exclude_unless:type,wifi', 'boolean'],

            'email_to' => ['exclude_unless:type,email', 'required', 'email', 'max:254'],
            'email_subject' => ['exclude_unless:type,email', 'nullable', 'string', 'max:200'],
            'email_body' => ['exclude_unless:type,email', 'nullable', 'string', 'max:1000'],

            'phone' => ['exclude_unless:type,phone,sms', 'required', ...$phone],
            'sms_message' => ['exclude_unless:type,sms', 'nullable', 'string', 'max:500'],

            'fg' => $hex,
            'bg' => $hex,
            'size' => ['required', 'integer', 'between:128,2048'],
            'margin' => ['required', 'integer', 'between:0,10'],
            'ecc' => ['required', Rule::in(QrStyle::ECC_LEVELS)],
            'format' => ['sometimes', Rule::in(['svg', 'png'])],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'Enter a valid web address, like https://example.com.',
            'wifi_password.required_unless' => 'Enter the Wi-Fi password, or choose "No password".',
            'phone.regex' => 'Enter a phone number using digits, spaces and an optional leading +.',
            'fg.regex' => 'Pick a valid colour.',
            'bg.regex' => 'Pick a valid colour.',
        ];
    }

    public function payload(): string
    {
        return QrPayload::fromArray($this->validated());
    }

    public function style(): QrStyle
    {
        return QrStyle::fromArray($this->validated());
    }
}
