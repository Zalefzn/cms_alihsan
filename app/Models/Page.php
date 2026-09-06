<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends Model
{
    use HasFactory;
    use LogsActivity;

    public const REVIEW_STATUSES = [
        'draft' => 'Draf',
        'in_review' => 'Menunggu Tinjauan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    /**
     * Logs create/update/delete of the page's own fields (title, slug, publish
     * status, meta description). Block content changes go through BuildPage's
     * bulk query-builder update, which bypasses Eloquent events entirely — see
     * BuildPage::save() for the explicit activity log entry covering those.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'is_published', 'meta_description', 'review_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
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
        'review_status',
        'review_note',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('order');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
