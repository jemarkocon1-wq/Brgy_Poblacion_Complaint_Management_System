<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $approved_at
 * @property Carbon|null $staff_responded_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $receipt_uploaded_at
 * @property Carbon|null $receipt_confirmed_at
 * @property Carbon|null $summons_ready_at
 */
#[Fillable([
    'reference',
    'resident_id',
    'assigned_staff_id',
    'category',
    'title',
    'respondents',
    'case_subject',
    'incident_date',
    'description',
    'relief_sought',
    'witnesses',
    'location',
    'status',
    'rejection_reason',
    'resolved_at',
    'approved_at',
    'staff_responded_at',
    'closed_at',
    'receipt_proof_path',
    'receipt_proof_original_name',
    'receipt_proof_mime_type',
    'receipt_proof_size',
    'receipt_uploaded_at',
    'receipt_confirmed_at',
    'summons_case_number',
    'summons_for',
    'summons_hearing_date',
    'summons_hearing_time',
    'summons_issued_date',
    'summons_signatory',
    'summons_ready_at',
])]
class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'approved_at' => 'datetime',
            'staff_responded_at' => 'datetime',
            'closed_at' => 'datetime',
            'receipt_uploaded_at' => 'datetime',
            'receipt_confirmed_at' => 'datetime',
            'summons_hearing_date' => 'date',
            'summons_issued_date' => 'date',
            'summons_ready_at' => 'datetime',
            'incident_date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /** @return HasMany<ComplaintEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(ComplaintEvidence::class);
    }

    /** @return HasMany<ComplaintComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(ComplaintComment::class)->orderBy('created_at')->orderBy('id');
    }

    /** @return HasMany<ComplaintActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(ComplaintActivity::class);
    }

    /** @return HasMany<ComplaintMeeting, $this> */
    public function meetings(): HasMany
    {
        return $this->hasMany(ComplaintMeeting::class)->latest('meeting_date')->latest('id');
    }

    /** @return HasMany<ComplaintSummonsService, $this> */
    public function summonsServices(): HasMany
    {
        return $this->hasMany(ComplaintSummonsService::class)->latest('served_at')->latest('id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => __('In Progress'),
            'closed' => __('Closed (no staff response)'),
            'unsettled' => __('Unsettled'),
            default => __(ucfirst($this->status)),
        };
    }
}
