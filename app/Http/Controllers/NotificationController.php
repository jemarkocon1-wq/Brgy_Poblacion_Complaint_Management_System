<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $complaintId = $notification->data['complaint_id'] ?? null;
        $complaint = is_int($complaintId) || ctype_digit((string) $complaintId)
            ? Complaint::query()->find($complaintId)
            : null;

        if ($complaint !== null && $user->can('view', $complaint)) {
            return redirect()->route($this->roleRoute($user->role).'.complaints.show', $complaint)
                ->with('status', 'Notification marked as read.');
        }

        return redirect()->route($this->roleRoute($user->role).'.dashboard')
            ->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', 'All notifications marked as read.');
    }

    private function roleRoute(string $role): string
    {
        abort_unless(in_array($role, ['resident', 'staff', 'admin'], true), 403);

        return $role;
    }
}
