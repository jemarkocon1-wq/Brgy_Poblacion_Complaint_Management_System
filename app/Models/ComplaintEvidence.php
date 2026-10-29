<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['complaint_id', 'complaint_comment_id', 'path', 'mime_type', 'original_name', 'size'])]
class ComplaintEvidence extends Model
{
    /** @return BelongsTo<Complaint, $this> */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /** @return BelongsTo<ComplaintComment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(ComplaintComment::class, 'complaint_comment_id');
    }
}
