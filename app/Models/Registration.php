<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A PPDB (student admission) submission from the frontend's registration form
 * block (see RegistrationController) — reviewed and moved through `status`
 * (baru/diproses/diterima/ditolak) from App\Filament\Resources\RegistrationResource.
 */
class Registration extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['child_name', 'parent_name', 'phone', 'email', 'unit', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('registration');
    }

    protected $fillable = [
        'child_name',
        'parent_name',
        'phone',
        'email',
        'unit',
        'message',
        'status',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'diterima' => 'Diterima',
        'ditolak' => 'Ditolak',
    ];

    public const UNITS = [
        'kober' => 'Kelompok Bermain',
        'tk' => 'TK',
        'sd' => 'SD',
    ];
}
