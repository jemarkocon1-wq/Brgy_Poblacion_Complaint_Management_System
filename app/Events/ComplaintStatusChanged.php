<?php

namespace App\Events;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintStatusChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Complaint $complaint, public ?User $actor, public string $previousStatus)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('complaints.resident.'.$this->complaint->resident_id),
            new PrivateChannel('complaints.staff'),
            new PrivateChannel('complaints.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'complaint.status-changed';
    }

    /**
     * @return array<string, int|string|null>
     */
    public function broadcastWith(): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'reference' => $this->complaint->reference,
            'status' => $this->complaint->status,
            'status_label' => $this->complaint->statusLabel(),
            'previous_status' => $this->previousStatus,
            'resident_id' => $this->complaint->resident_id,
            'assigned_staff_id' => $this->complaint->assigned_staff_id,
            'actor_name' => $this->actor?->name,
        ];
    }
}
