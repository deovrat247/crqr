<?php

namespace App\Models;

use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'url',
        'title',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'clicks' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shortUrl(): string
    {
        return url($this->code);
    }

    /**
     * Generator link for this short URL. The ?s=qr marker lets analytics tell scans apart from clicks.
     */
    public function qrUrl(): string
    {
        return route('qr', ['url' => $this->shortUrl().'?s=qr']);
    }

    public function displayTitle(): string
    {
        return $this->title ?: (parse_url($this->url, PHP_URL_HOST) ?: $this->url);
    }

    /**
     * Random unused code. Grows by one character if the current length keeps colliding.
     */
    public static function generateCode(): string
    {
        $alphabet = config('crqr.code_alphabet');
        $length = config('crqr.code_length');
        $reserved = config('crqr.reserved');

        for ($attempt = 0; ; $attempt++) {
            $size = $length + intdiv($attempt, 5);
            $code = '';

            for ($i = 0; $i < $size; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            if (! in_array($code, $reserved, true) && ! static::where('code', $code)->exists()) {
                return $code;
            }
        }
    }
}
