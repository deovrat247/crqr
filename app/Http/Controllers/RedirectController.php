<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    // Crawlers, link-preview fetchers and scripts. They still get redirected, but aren't counted.
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|discord|skype|curl|wget|python|go-http|java\/|headless/i';

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $link = Link::where('code', strtolower($code))->where('is_active', true)->first();

        abort_unless($link, 404);

        if ($request->isMethod('GET') && ! preg_match(self::BOT_PATTERN, (string) $request->userAgent())) {
            // Query builder increment, so updated_at keeps meaning "last edited".
            Link::whereKey($link->id)->toBase()->increment('clicks');
        }

        // 302 + no-store: browsers must ask again each time, so edits apply and every click counts.
        return redirect()->away($link->url, 302)->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
