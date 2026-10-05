<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('complaints.resident.{residentId}', function (User $user, int $residentId): bool {
    $resident = Auth::guard('resident')->user();

    return $resident?->isRole('resident') && $resident->id === $residentId;
});

Broadcast::channel('complaints.staff', function (): bool {
    return Auth::guard('staff')->user()?->isRole('staff')
        || Auth::guard('admin')->user()?->isRole('admin');
});

Broadcast::channel('complaints.admin', function (): bool {
    return Auth::guard('admin')->user()?->isRole('admin');
});
