<?php

namespace App\Events;

use App\Models\Complaint;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintSubmitted implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Complaint $complaint)
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
        return 'complaint.submitted';
    }

    /**
     * @return array<string, int|string>
     */
    public function broadcastWith(): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'reference' => $this->complaint->reference,
            'status' => $this->complaint->status,
            'resident_id' => $this->complaint->resident_id,
        ];
    }
}
