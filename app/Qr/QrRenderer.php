<?php

namespace App\Qr;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Renders QR codes as SVG markup or PNG binary.
 *
 * @throws \chillerlan\QRCode\QRCodeException when the data does not fit in a QR code
 */
final class QrRenderer
{
    public function svg(string $data, QrStyle $style): string
    {
        $qr = new QRCode($this->options($style, QROutputInterface::MARKUP_SVG, [
            'outputBase64' => false,
            'svgAddXmlHeader' => true,
            'connectPaths' => true,
            'moduleValues' => $this->moduleValues($style, fn (string $hex) => $hex),
        ]));

        return $qr->addByteSegment($data)->renderMatrix($qr->getQRMatrix());
    }

    public function png(string $data, QrStyle $style): string
    {
        $options = $this->options($style, QROutputInterface::GDIMAGE_PNG, [
            'outputBase64' => false,
            'returnResource' => true,
            'moduleValues' => $this->moduleValues($style, $this->hexToRgb(...)),
        ]);

        $qr = (new QRCode($options))->addByteSegment($data);
        $matrix = $qr->getQRMatrix();

        // Render at a whole-pixel scale at or above the target, then shrink to the exact size.
        $options->scale = max(1, (int) ceil($style->size / $matrix->getSize()));
        $image = $qr->renderMatrix($matrix);

        if (imagesx($image) !== $style->size) {
            $image = imagescale($image, $style->size, $style->size, IMG_NEAREST_NEIGHBOUR);
        }

        ob_start();
        imagepng($image, null, 6);

        return ob_get_clean();
    }

    private function options(QrStyle $style, string $outputType, array $extra): QROptions
    {
        return new QROptions([
            'outputType' => $outputType,
            'eccLevel' => constant(EccLevel::class.'::'.$style->ecc),
            'addQuietzone' => $style->margin > 0,
            'quietzoneSize' => $style->margin,
            ...$extra,
        ]);
    }

    /**
     * Maps every module type (finder, data, quiet zone, ...) to the foreground or background colour.
     */
    private function moduleValues(QrStyle $style, callable $format): array
    {
        $values = [];

        foreach (QROutputInterface::DEFAULT_MODULE_VALUES as $type => $isDark) {
            $values[$type] = $format($isDark ? $style->foreground : $style->background);
        }

        return $values;
    }

    private function hexToRgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }
}
