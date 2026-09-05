<?php

namespace App\Models;

use App\Support\MediaResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * The block's `data` for the given locale, with any stored file paths (from
     * FileUpload fields) turned into publicly accessible URLs. Walks the whole
     * data tree recursively — including nested repeater items — so it applies
     * uniformly across all block types without a per-type field list to maintain.
     */
    public function resolvedData(string $locale = 'id'): array
    {
        return MediaResolver::resolveDeep(self::localize($this->data ?? [], $locale));
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
