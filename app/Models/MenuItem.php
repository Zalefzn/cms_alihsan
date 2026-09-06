<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MenuItem extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * Covers edits made via the classic "Kelola" table form (a normal Eloquent
     * save). BuildMenu's bulk query-builder update bypasses model events — see
     * its own explicit activity log entry for changes made there.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['label', 'url', 'is_visible', 'open_in_new_tab', 'dropdown_style'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('menu_item');
    }

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
