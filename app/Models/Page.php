<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Page extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * Logs create/update/delete of the page's own fields (title, slug, publish
     * status, meta description). Block content changes go through BuildPage's
     * bulk query-builder update, which bypasses Eloquent events entirely — see
     * BuildPage::save() for the explicit activity log entry covering those.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'is_published', 'meta_description'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('page');
    }

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
