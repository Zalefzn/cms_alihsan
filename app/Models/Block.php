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
     * The block's `data`, with any stored file paths (from FileUpload
     * fields) turned into publicly accessible URLs. Which keys hold a
     * file path depends on the block's type — see BlockDefinitions.
     */
    public function resolvedData(): array
    {
        $data = $this->data ?? [];

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
}
