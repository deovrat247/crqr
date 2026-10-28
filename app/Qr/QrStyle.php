<?php

namespace App\Qr;

/**
 * Visual options for a rendered QR code.
 */
final readonly class QrStyle
{
    public const ECC_LEVELS = ['L', 'M', 'Q', 'H'];

    public function __construct(
        public string $foreground = '#000000',
        public string $background = '#ffffff',
        public int $size = 512,
        public int $margin = 2,
        public string $ecc = 'M',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            foreground: strtolower($data['fg']),
            background: strtolower($data['bg']),
            size: (int) $data['size'],
            margin: (int) $data['margin'],
            ecc: $data['ecc'],
        );
    }
}
