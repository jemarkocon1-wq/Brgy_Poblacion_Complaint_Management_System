<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $resident = $request->user('resident');
        $complaints = Complaint::query()->whereBelongsTo($resident, 'resident');

        return view('resident.dashboard', [
            'counts' => [
                'total' => (clone $complaints)->count(),
                'pending' => (clone $complaints)->where('status', 'pending')->count(),
                'in_progress' => (clone $complaints)->where('status', 'approved')->count(),
                'resolved' => (clone $complaints)->where('status', 'resolved')->count(),
            ],
            'recentComplaints' => (clone $complaints)->with('assignedStaff')->latest()->limit(5)->get(),
        ]);
    }
}
