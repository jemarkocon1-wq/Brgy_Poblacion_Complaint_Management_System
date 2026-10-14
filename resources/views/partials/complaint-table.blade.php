<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-stone-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800 dark:text-slate-400"><tr>
                <th class="px-5 py-4">Complaint</th>
                @if (! request()->routeIs('resident.*'))<th class="px-5 py-4">Resident</th>@endif
                <th class="px-5 py-4">Category</th><th class="px-5 py-4">Status</th>
                @if (! request()->routeIs('resident.*'))<th class="px-5 py-4">Assigned to</th>@endif
                <th class="px-5 py-4"></th>
            </tr></thead>
            <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                @forelse ($complaints as $complaint)
                    <tr class="hover:bg-stone-50/70 dark:hover:bg-slate-800/40">
                        <td class="px-5 py-4"><p class="font-bold">{{ $complaint->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $complaint->reference }} · {{ $complaint->created_at->format('M j, Y') }}</p></td>
                        @if (! request()->routeIs('resident.*'))<td class="px-5 py-4">{{ $complaint->resident?->name }}</td>@endif
                        <td class="px-5 py-4 capitalize text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', $complaint->category) }}</td>
                        <td class="px-5 py-4">@include('partials.status-badge', ['complaint' => $complaint])</td>
                        @if (! request()->routeIs('resident.*'))<td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $complaint->assignedStaff?->name ?? '—' }}</td>@endif
                        <td class="px-5 py-4 text-right">
                            @php
                                $detailRoute = request()->routeIs('resident.*') ? 'resident.complaints.show' : (request()->routeIs('admin.*') ? 'admin.complaints.show' : 'staff.complaints.show');
                            @endphp
                            <a href="{{ route($detailRoute, $complaint) }}" class="font-bold text-maroon hover:underline">{{ request()->routeIs('resident.*') ? 'View complaint & comments →' : 'View details →' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No complaints to show yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($complaints instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="border-t border-stone-100 px-5 py-4 dark:border-slate-800">{{ $complaints->links() }}</div>@endif
</div>
