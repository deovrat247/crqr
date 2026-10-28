<?php

namespace App\Qr;

/**
 * Builds the string that gets encoded into a QR code for each content type.
 */
final class QrPayload
{
    public const TYPES = ['url', 'text', 'wifi', 'email', 'phone', 'sms'];

    public const WIFI_ENCRYPTIONS = ['WPA', 'WEP', 'nopass'];

    /**
     * @param  array<string, mixed>  $data  validated input from QrRequest
     */
    public static function fromArray(array $data): string
    {
        return match ($data['type']) {
            'url' => $data['url'],
            'text' => $data['text'],
            'wifi' => self::wifi($data),
            'email' => self::email($data),
            'phone' => 'tel:'.self::cleanPhone($data['phone']),
            'sms' => 'SMSTO:'.self::cleanPhone($data['phone']).':'.($data['sms_message'] ?? ''),
        };
    }

    private static function wifi(array $data): string
    {
        $encryption = $data['wifi_encryption'] ?? 'WPA';
        $payload = 'WIFI:T:'.$encryption.';S:'.self::escapeWifi($data['wifi_ssid']).';';

        if ($encryption !== 'nopass') {
            $payload .= 'P:'.self::escapeWifi($data['wifi_password'] ?? '').';';
        }

        if (! empty($data['wifi_hidden'])) {
            $payload .= 'H:true;';
        }

        return $payload.';';
    }

    private static function email(array $data): string
    {
        $query = http_build_query(array_filter([
            'subject' => $data['email_subject'] ?? null,
            'body' => $data['email_body'] ?? null,
        ]), '', '&', PHP_QUERY_RFC3986);

        return 'mailto:'.$data['email_to'].($query !== '' ? '?'.$query : '');
    }

    /**
     * Special characters in Wi-Fi fields must be backslash-escaped.
     */
    private static function escapeWifi(string $value): string
    {
        return preg_replace('/([\\\\;,:"])/', '\\\\$1', $value);
    }

    private static function cleanPhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }
}
