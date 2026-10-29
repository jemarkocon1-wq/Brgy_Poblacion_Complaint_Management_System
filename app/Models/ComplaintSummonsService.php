<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'complaint_id',
    'recorded_by_id',
    'served_at',
    'server_name',
    'recipient_name',
    'result',
    'signer_type',
    'signer_name',
    'refusal_response',
    'proof_path',
    'proof_original_name',
    'proof_mime_type',
    'proof_size',
])]
class ComplaintSummonsService extends Model
{
    protected function casts(): array
    {
        return [
            'served_at' => 'datetime',
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
