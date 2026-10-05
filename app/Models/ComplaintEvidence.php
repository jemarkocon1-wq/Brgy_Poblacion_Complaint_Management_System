<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['complaint_id', 'path', 'mime_type', 'original_name', 'size'])]
class ComplaintEvidence extends Model
{
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
