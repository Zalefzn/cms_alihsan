<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Resolves whatever a FileUpload field's value currently is — an already-saved
 * relative storage path, a fresh (not yet saved) upload (a TemporaryUploadedFile
 * instance, or the signed "livewire-file:..." string form it takes when read back
 * out through Filament's $get()), or an already-absolute URL — into a URL that can
 * actually be displayed right now. Shared by the block-type preview thumbnails, the
 * page builder's live canvas, and Block::resolvedData() for the public API.
 */
class MediaResolver
{
    /** @var string[] */
    protected static array $mediaKeys = ['image', 'video', 'photo', 'file'];

    /**
     * Walk an entire block `data` array (including nested repeater items) and
     * resolve every value under a known media key, at any depth.
     */
    public static function resolveDeep(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, self::$mediaKeys, true)) {
                $result[$key] = self::resolveValue($value);

                continue;
            }

            if ($key === 'images' && is_array($value)) {
                $result[$key] = array_map(fn ($v) => self::resolveValue($v), $value);

                continue;
            }

            if (is_array($value)) {
                $result[$key] = array_is_list($value)
                    ? array_map(fn ($item) => is_array($item) ? self::resolveDeep($item) : $item, $value)
                    : self::resolveDeep($value);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    public static function resolveValue(mixed $value): mixed
    {
        if (blank($value)) {
            return $value;
        }

        // Already a full URL (e.g. typed by hand rather than uploaded) — leave as-is
        // instead of prepending the storage disk prefix on top of it.
        if (is_string($value) && preg_match('#^https?://#', $value)) {
            return $value;
        }

        if (is_string($value) && TemporaryUploadedFile::canUnserialize($value)) {
            try {
                $file = TemporaryUploadedFile::unserializeFromLivewireRequest($value);
                $file = is_array($file) ? ($file[0] ?? null) : $file;

                return $file?->temporaryUrl();
            } catch (Throwable) {
                return null;
            }
        }

        if (is_object($value) && method_exists($value, 'temporaryUrl')) {
            try {
                return $value->temporaryUrl();
            } catch (Throwable) {
                return null;
            }
        }

        if (is_string($value) && $value !== '') {
            return Storage::disk('public')->url($value);
        }

        return $value;
    }

    /**
     * A real thumbnail image for a YouTube embed URL, extracted straight from the
     * video ID — no separate thumbnail field to maintain. Used by the page builder's
     * sidebar block list and the block-type preview's real-content card so a video
     * block shows an actual frame instead of a generic play icon. Returns null for
     * non-YouTube links (Vimeo, a self-hosted upload with no embed_url, etc.).
     */
    public static function youtubeThumbnail(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return "https://img.youtube.com/vi/{$matches[1]}/mqdefault.jpg";
        }

        return null;
    }
}
