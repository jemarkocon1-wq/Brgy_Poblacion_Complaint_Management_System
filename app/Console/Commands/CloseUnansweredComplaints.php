<?php

namespace App\Console\Commands;

use App\Events\ComplaintStatusChanged;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Notifications\ComplaintAlert;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

#[Signature('complaints:close-unanswered')]
#[Description('Close approved complaints that have no staff response after three days')]
class CloseUnansweredComplaints extends Command
{
    private const RESPONSE_DAYS = 3;

    public function handle(): int
    {
        $responseDeadline = now()->subDays(self::RESPONSE_DAYS);
        $closedCount = 0;

        Complaint::query()
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', $responseDeadline)
            ->whereNull('staff_responded_at')
            ->orderBy('id')
            ->chunkById(100, function (Collection $complaints) use ($responseDeadline, &$closedCount): void {
                foreach ($complaints as $complaint) {
                    $closed = DB::transaction(function () use ($complaint, $responseDeadline): bool {
                        $lockedComplaint = Complaint::query()->lockForUpdate()->find($complaint->id);

                        if ($lockedComplaint === null
                            || $lockedComplaint->status !== 'approved'
                            || $lockedComplaint->staff_responded_at !== null
                            || $lockedComplaint->approved_at?->greaterThan($responseDeadline)) {
                            return false;
                        }

                        $updated = Complaint::query()
                            ->whereKey($lockedComplaint->id)
                            ->where('status', 'approved')
                            ->whereNull('staff_responded_at')
                            ->where('approved_at', '<=', $responseDeadline)
                            ->update([
                                'status' => 'closed',
                                'closed_at' => now(),
                            ]);

                        if ($updated !== 1) {
                            return false;
                        }

                        ComplaintActivity::create([
                            'complaint_id' => $lockedComplaint->id,
                            'action' => 'auto_closed',
                            'from_status' => 'approved',
                            'to_status' => 'closed',
                            'description' => 'Ticket automatically closed after three days without a staff response.',
                        ]);

                        return true;
                    });

                    if (! $closed) {
                        continue;
                    }

                    $complaint->refresh();
                    event(new ComplaintStatusChanged($complaint, null, 'approved'));
                    $complaint->resident->notify(new ComplaintAlert(
                        $complaint,
                        'Ticket '.$complaint->reference.' was closed after three days without a staff response.',
                    ));
                    $closedCount++;
                }
            });

        $this->info("Closed {$closedCount} unanswered complaint(s).");

        return self::SUCCESS;
    }
}
