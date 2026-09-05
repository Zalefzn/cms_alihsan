<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'label',
        'label_en',
        'url',
        'order',
        'is_visible',
        'open_in_new_tab',
        'dropdown_style',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'open_in_new_tab' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function localizedLabel(string $locale = 'id'): ?string
    {
        return ($locale === 'en' && filled($this->label_en)) ? $this->label_en : $this->label;
    }
}
