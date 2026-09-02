<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'type',
        'order',
        'is_visible',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_visible' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * The block's `data` for the given locale, with any stored file
     * paths (from FileUpload fields) turned into publicly accessible
     * URLs. Which keys hold a file path depends on the block's type —
     * see BlockDefinitions.
     */
    public function resolvedData(string $locale = 'id'): array
    {
        $data = self::localize($this->data ?? [], $locale);

        foreach (['image', 'video'] as $field) {
            if (! empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = Storage::disk('public')->url($data[$field]);
            }
        }

        if (! empty($data['images']) && is_array($data['images'])) {
            $data['images'] = array_map(
                fn ($path) => Storage::disk('public')->url($path),
                $data['images']
            );
        }

        if (in_array($this->type, ['team', 'testimonials'], true) && ! empty($data['items'])) {
            $data['items'] = array_map(function ($item) {
                if (! empty($item['photo'])) {
                    $item['photo'] = Storage::disk('public')->url($item['photo']);
                }

                return $item;
            }, $data['items']);
        }

        return $data;
    }

    /**
     * Every translatable field is stored as `foo` (Indonesian) plus a
     * `foo_en` sibling. For English, swap in the `_en` value whenever
     * it's non-empty (falling back to the Indonesian text otherwise),
     * then drop the `_en` keys from the output. Works recursively so
     * it applies inside Repeater items without any per-block-type
     * field list to maintain.
     */
    public static function localize(array $data, string $locale): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && str_ends_with($key, '_en')) {
                continue;
            }

            if (is_array($value)) {
                $result[$key] = array_is_list($value)
                    ? array_map(fn ($item) => is_array($item) ? self::localize($item, $locale) : $item, $value)
                    : self::localize($value, $locale);

                continue;
            }

            $enValue = $data[$key . '_en'] ?? null;

            $result[$key] = ($locale === 'en' && filled($enValue)) ? $enValue : $value;
        }

        return $result;
    }
}
