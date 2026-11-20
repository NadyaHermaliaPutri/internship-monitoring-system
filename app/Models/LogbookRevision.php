<?php

namespace App\Models;

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
}
