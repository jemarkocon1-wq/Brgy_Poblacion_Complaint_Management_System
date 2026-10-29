<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public string $message,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'reference' => $this->complaint->reference,
            'message' => $this->message,
            'status' => $this->complaint->status,
        ];
    }
}
