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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');
        $recentComplaints = (clone $complaints)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            }))
            ->with('assignedStaff')
            ->latest();

        return view('resident.dashboard', [
            'counts' => [
                'total' => (clone $complaints)->count(),
                'pending' => (clone $complaints)->where('status', 'pending')->count(),
                'in_progress' => (clone $complaints)->where('status', 'approved')->count(),
                'resolved' => (clone $complaints)->where('status', 'resolved')->count(),
            ],
            'recentComplaints' => $search === ''
                ? $recentComplaints->limit(5)->get()
                : $recentComplaints->paginate(5)->withQueryString(),
            'search' => $search,
        ]);
    }
}
