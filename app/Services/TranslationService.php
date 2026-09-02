<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Auto-translates short admin-entered text (Indonesian -> English) using
 * MyMemory's free public translation API, so editors get a draft English
 * value instead of an empty field. Always returns null on any failure —
 * callers must treat translation as a best-effort convenience, never a
 * requirement, since the CMS must stay usable when the service is down.
 */
class TranslationService
{
    public static function idToEn(?string $text): ?string
    {
        return self::translate($text, 'id', 'en');
    }

    public static function translate(?string $text, string $from, string $to): ?string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        // MyMemory limits a single request to 500 characters.
        if (mb_strlen($text) > 500) {
            return null;
        }

        try {
            $response = Http::timeout(6)->get('https://api.mymemory.translated.net/get', [
                'q' => $text,
                'langpair' => "{$from}|{$to}",
            ]);

            if (! $response->successful()) {
                return null;
            }

            $translated = $response->json('responseData.translatedText');

            if (! is_string($translated) || $translated === '') {
                return null;
            }

            return html_entity_decode($translated, ENT_QUOTES);
        } catch (\Throwable $e) {
            Log::warning('TranslationService: gagal menerjemahkan teks.', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
