<?php

namespace Tests\Unit;

use App\Qr\QrPayload;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QrPayloadTest extends TestCase
{
    public static function payloads(): array
    {
        return [
            'url' => [['type' => 'url', 'url' => 'https://crqr.in'], 'https://crqr.in'],
            'text' => [['type' => 'text', 'text' => 'Hello'], 'Hello'],
            'wifi wpa' => [
                ['type' => 'wifi', 'wifi_ssid' => 'Home', 'wifi_encryption' => 'WPA', 'wifi_password' => 'secret', 'wifi_hidden' => false],
                'WIFI:T:WPA;S:Home;P:secret;;',
            ],
            'wifi escapes special characters' => [
                ['type' => 'wifi', 'wifi_ssid' => 'My;Net', 'wifi_encryption' => 'WPA', 'wifi_password' => 'a:b\\c"d,e', 'wifi_hidden' => true],
                'WIFI:T:WPA;S:My\;Net;P:a\:b\\\\c\"d\,e;H:true;;',
            ],
            'wifi open network' => [
                ['type' => 'wifi', 'wifi_ssid' => 'Cafe', 'wifi_encryption' => 'nopass', 'wifi_hidden' => false],
                'WIFI:T:nopass;S:Cafe;;',
            ],
            'email with subject and body' => [
                ['type' => 'email', 'email_to' => 'hi@crqr.in', 'email_subject' => 'Hello there', 'email_body' => 'A & B'],
                'mailto:hi@crqr.in?subject=Hello%20there&body=A%20%26%20B',
            ],
            'email address only' => [['type' => 'email', 'email_to' => 'hi@crqr.in'], 'mailto:hi@crqr.in'],
            'phone strips formatting' => [['type' => 'phone', 'phone' => '+91 (987) 654-3210'], 'tel:+919876543210'],
            'sms' => [['type' => 'sms', 'phone' => '+91 98765 43210', 'sms_message' => 'Hi'], 'SMSTO:+919876543210:Hi'],
        ];
    }

    #[DataProvider('payloads')]
    public function test_builds_payload(array $data, string $expected): void
    {
        $this->assertSame($expected, QrPayload::fromArray($data));
    }
}
