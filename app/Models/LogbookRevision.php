<?php

namespace App\Models;

use App\Enums\LogbookActivityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'logbook_id',
    'revision_no',
    'log_date',
    'title',
    'activity_description',
    'result',
    'evidence_media_id',
    'target_pic_user_id',
    'activity_status',
    'status_before',
    'review_note_before',
    'revised_by',
])]
class LogbookRevision extends Model
{
    protected function casts(): array
    {
        return [
            'log_date' => 'date:Y-m-d',
            'revision_no' => 'integer',
            'activity_status' => LogbookActivityStatus::class,
        ];
    }

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function evidenceMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'evidence_media_id');
    }

    public function targetPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_pic_user_id');
    }
}
