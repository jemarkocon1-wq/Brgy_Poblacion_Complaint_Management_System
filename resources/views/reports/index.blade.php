@extends('layouts.app')

@section('title', 'Reports')
@section('heading', $title)

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-slate-500 dark:text-slate-400">A snapshot of complaint volume and outcomes.</p><h2 class="mt-1 text-2xl font-black">Complaint reports</h2></div>
    @if (auth()->user()->role === 'admin')<div class="flex gap-2"><a href="{{ route('admin.reports.csv', request()->query()) }}" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-bold dark:border-slate-700 dark:bg-slate-900">Export CSV</a><a href="{{ route('admin.reports.pdf', request()->query()) }}" class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Export PDF</a></div>@endif
</div>
<form method="GET" action="{{ auth()->user()->role === 'admin' ? route('admin.reports') : route('staff.reports') }}" class="mb-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-[1fr_180px_180px_auto_auto] dark:border-slate-800 dark:bg-slate-900">
    <select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
        <option value="">All statuses</option>
        <option value="pending" @selected(request('status') === 'pending')>{{ __('Pending') }}</option>
        <option value="approved" @selected(request('status') === 'approved')>{{ __('In Progress') }}</option>
        <option value="unsettled" @selected(request('status') === 'unsettled')>{{ __('Unsettled') }}</option>
        <option value="resolved" @selected(request('status') === 'resolved')>{{ __('Resolved') }}</option>
        <option value="rejected" @selected(request('status') === 'rejected')>{{ __('Rejected') }}</option>
        <option value="closed" @selected(request('status') === 'closed')>{{ __('Closed (no staff response)') }}</option>
    </select>
    <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <button class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Apply</button>
    @if (request()->hasAny(['status', 'start_date', 'end_date']))
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.reports') : route('staff.reports') }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700">Clear</a>
    @endif
</form>
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
    @foreach (['pending' => 'Pending', 'approved' => 'In Progress', 'unsettled' => 'Unsettled', 'resolved' => 'Resolved', 'rejected' => 'Rejected', 'closed' => 'Closed (no staff response)'] as $key => $label)
        <article class="rounded-2xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-sm text-slate-500 dark:text-slate-400">{{ __($label) }}</p><p data-live-count="{{ $key === 'approved' ? 'in_progress' : $key }}" class="mt-2 text-3xl font-black">{{ $counts[$key] ?? 0 }}</p></article>
    @endforeach
</section>
<section class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><h3 class="text-lg font-bold">Reports by category</h3><div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse ($categories as $category => $total)<div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3 dark:bg-slate-800"><span class="text-sm font-medium capitalize">{{ str_replace('_', ' ', $category) }}</span><span class="font-bold">{{ $total }}</span></div>@empty<p class="text-sm text-slate-500">There are no complaints to report yet.</p>@endforelse</div></section>
<section class="mt-6 overflow-hidden rounded-2xl border border-stone-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="border-b border-stone-200 px-5 py-4 dark:border-slate-800"><h3 class="text-lg font-bold">Complaint review</h3></div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-stone-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 font-semibold">Reference</th>
                    <th class="px-4 py-3 font-semibold">Title</th>
                    <th class="px-4 py-3 font-semibold">Complainant</th>
                    <th class="px-4 py-3 font-semibold">Respondent(s)</th>
                    <th class="px-4 py-3 font-semibold">Witnesses</th>
                    <th class="px-4 py-3 font-semibold">Category</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Assigned staff</th>
                    <th class="px-4 py-3 font-semibold">Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($complaints as $complaint)
                    <tr class="border-t border-stone-200 dark:border-slate-800">
                        <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $complaint->reference }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->title }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->resident?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->respondents ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->witnesses ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ str_replace('_', ' ', ucfirst($complaint->category)) }}</td>
                        <td class="px-4 py-3">{{ $complaint->statusLabel() }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->assignedStaff?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $complaint->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">There are no complaint records for the selected filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($complaints->hasPages())
        <div class="border-t border-stone-200 px-5 py-4 dark:border-slate-800">
            {{ $complaints->links() }}
        </div>
    @endif
</section>
@endsection
