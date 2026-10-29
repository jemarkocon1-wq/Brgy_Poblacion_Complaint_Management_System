<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'complaint_id',
    'recorded_by_id',
    'meeting_type',
    'meeting_date',
    'participants',
    'minutes',
    'outcome',
    'settlement_terms',
])]
class ComplaintMeeting extends Model
{
    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    /** @return BelongsTo<Complaint, $this> */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
