<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrRequest;
use App\Qr\QrPayload;
use App\Qr\QrRenderer;
use App\Qr\QrStyle;
use chillerlan\QRCode\QRCodeException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QrController extends Controller
{
    public function __construct(private QrRenderer $renderer) {}

    public function show(Request $request): View
    {
        $type = $request->query('type');

        return view('qr.show', [
            'types' => QrPayload::TYPES,
            'eccLevels' => QrStyle::ECC_LEVELS,
            'defaults' => new QrStyle,
            // Lets other pages link here with content pre-filled, e.g. /qr?url=https://crqr.in/abc
            'prefill' => [
                'type' => in_array($type, QrPayload::TYPES, true) ? $type : 'url',
                'url' => (string) $request->query('url', ''),
                'text' => (string) $request->query('text', ''),
            ],
        ]);
    }

    public function preview(QrRequest $request): Response
    {
        $svg = $this->render(fn () => $this->renderer->svg($request->payload(), $request->style()));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function download(QrRequest $request): Response
    {
        $format = $request->validated('format', 'png');

        $body = $this->render(fn () => $format === 'svg'
            ? $this->renderer->svg($request->payload(), $request->style())
            : $this->renderer->png($request->payload(), $request->style()));

        $filename = 'crqr-'.$request->validated('type').'-'.now()->format('Ymd-His').'.'.$format;

        return response($body, 200, [
            'Content-Type' => $format === 'svg' ? 'image/svg+xml' : 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function render(callable $callback): string
    {
        try {
            return $callback();
        } catch (QRCodeException) {
            throw ValidationException::withMessages([
                'content' => 'That is too much content for one QR code. Shorten it or lower the error correction level.',
            ]);
        }
    }
}
