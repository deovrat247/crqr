<?php

namespace App\Http\Requests\Concerns;

trait NormalizesUrls
{
    /**
     * Trims the value and adds https:// when no scheme is given ("example.com" → "https://example.com").
     */
    protected function normalizeUrl(mixed $url): string
    {
        $url = trim((string) $url);

        if ($url !== '' && ! preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    /**
     * Rules for a link destination: a real http(s) URL that isn't one of our own short links.
     */
    protected function destinationRules(): array
    {
        return [
            'required',
            'url:http,https',
            'max:2048',
            function (string $attribute, string $value, \Closure $fail) {
                $ownHosts = [...config('crqr.own_hosts'), parse_url(config('app.url'), PHP_URL_HOST)];
                $host = strtolower((string) parse_url($value, PHP_URL_HOST));

                if (in_array($host, $ownHosts, true)) {
                    $fail('This is already a '.config('app.name').' link, so it can\'t be shortened again.');
                }
            },
        ];
    }
}
