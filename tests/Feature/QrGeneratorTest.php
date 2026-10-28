<?php

namespace Tests\Feature;

use chillerlan\QRCode\QRCode;
use Tests\TestCase;

class QrGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function input(array $overrides = []): array
    {
        return [
            'type' => 'url',
            'url' => 'https://crqr.in/hello',
            'fg' => '#1e1b4b',
            'bg' => '#ffffff',
            'size' => 256,
            'margin' => 2,
            'ecc' => 'M',
            ...$overrides,
        ];
    }

    public function test_pages_load(): void
    {
        $this->get('/')->assertOk()->assertSee('Create a QR code');
        $this->get('/qr')->assertOk()->assertSee('QR Code Generator');
    }

    public function test_generator_can_be_prefilled_from_the_query_string(): void
    {
        $this->get('/qr?url=https://crqr.in/abc')->assertOk()->assertSee('value="https://crqr.in/abc"', false);
    }

    public function test_preview_returns_svg_in_the_chosen_colours(): void
    {
        $response = $this->postJson('/qr/preview', $this->input());

        $response->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
        $this->assertStringContainsString('#1e1b4b', $response->getContent());
    }

    public function test_png_download_is_exact_size_and_scans_back_to_the_payload(): void
    {
        $response = $this->post('/qr/download', $this->input(['format' => 'png', 'size' => 300]));

        $response->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringContainsString('attachment; filename="crqr-url-', $response->headers->get('Content-Disposition'));

        [$width, $height] = getimagesizefromstring($response->getContent());
        $this->assertSame([300, 300], [$width, $height]);

        $decoded = (new QRCode)->readFromBlob($response->getContent());
        $this->assertSame('https://crqr.in/hello', (string) $decoded);
    }

    public function test_wifi_png_scans_back_to_the_wifi_payload(): void
    {
        $response = $this->post('/qr/download', $this->input([
            'type' => 'wifi',
            'wifi_ssid' => 'Office',
            'wifi_encryption' => 'WPA',
            'wifi_password' => 'p@ss;word',
            'format' => 'png',
            'size' => 512,
        ]));

        $response->assertOk();
        $this->assertSame('WIFI:T:WPA;S:Office;P:p@ss\;word;;', (string) (new QRCode)->readFromBlob($response->getContent()));
    }

    public function test_svg_download(): void
    {
        $this->post('/qr/download', $this->input(['format' => 'svg']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_url_without_scheme_gets_https(): void
    {
        $response = $this->post('/qr/download', $this->input(['url' => 'crqr.in', 'format' => 'png']));

        $this->assertSame('https://crqr.in', (string) (new QRCode)->readFromBlob($response->getContent()));
    }

    public function test_rejects_non_web_urls(): void
    {
        $this->postJson('/qr/preview', $this->input(['url' => 'javascript:alert(1)']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('url');
    }

    public function test_requires_the_fields_for_the_chosen_type(): void
    {
        $this->postJson('/qr/preview', $this->input(['type' => 'wifi']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['wifi_ssid', 'wifi_encryption']);

        $this->postJson('/qr/preview', $this->input(['type' => 'wifi', 'wifi_ssid' => 'Home', 'wifi_encryption' => 'WPA']))
            ->assertJsonValidationErrors('wifi_password');

        $this->postJson('/qr/preview', $this->input(['type' => 'wifi', 'wifi_ssid' => 'Home', 'wifi_encryption' => 'nopass']))
            ->assertOk();
    }

    public function test_rejects_invalid_style_options(): void
    {
        $this->postJson('/qr/preview', $this->input(['fg' => 'red', 'size' => 99999, 'margin' => 50, 'ecc' => 'X']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fg', 'size', 'margin', 'ecc']);
    }

    public function test_content_too_large_for_a_qr_code_is_a_validation_error(): void
    {
        $this->postJson('/qr/preview', $this->input(['type' => 'text', 'text' => str_repeat('漢', 1500), 'ecc' => 'H']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content');
    }

    public function test_failed_download_redirects_back_with_errors(): void
    {
        $this->from('/qr')
            ->post('/qr/download', $this->input(['url' => '', 'format' => 'png']))
            ->assertRedirect('/qr')
            ->assertSessionHasErrors('url');
    }
}
