<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'reference',
    'resident_id',
    'assigned_staff_id',
    'category',
    'title',
    'description',
    'location',
    'status',
    'rejection_reason',
    'resolved_at',
])]
class Complaint extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ComplaintEvidence::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ComplaintActivity::class);
    }

    public function statusLabel(): string
    {
        return $this->status === 'approved' ? 'In Progress' : ucfirst($this->status);
    }
}
