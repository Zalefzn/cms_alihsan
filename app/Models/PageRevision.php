<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A snapshot of a page's blocks taken right before BuildPage::save() overwrites
 * them — lets an admin restore an earlier version of a page's content. Only
 * the most recent N per page are kept (pruned in BuildPage::snapshotRevision()).
 */
class PageRevision extends Model
{
    protected $fillable = [
        'page_id',
        'user_id',
        'blocks',
    ];

    protected $casts = [
        'blocks' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
