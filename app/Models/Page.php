<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'icon',
        'title',
        'title_en',
        'meta_description',
        'meta_description_en',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('order');
    }

    public function localizedTitle(string $locale = 'id'): ?string
    {
        return ($locale === 'en' && filled($this->title_en)) ? $this->title_en : $this->title;
    }

    public function localizedMetaDescription(string $locale = 'id'): ?string
    {
        return ($locale === 'en' && filled($this->meta_description_en)) ? $this->meta_description_en : $this->meta_description;
    }
}
