@extends('layouts.app')

@section('title', 'Reports')
@section('heading', $title)

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-slate-500 dark:text-slate-400">A snapshot of complaint volume and outcomes.</p><h2 class="mt-1 text-2xl font-black">Complaint reports</h2></div>
    @if (auth()->user()->role === 'admin')<div class="flex gap-2"><a href="{{ route('admin.reports.csv') }}" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-bold dark:border-slate-700 dark:bg-slate-900">Export CSV</a><a href="{{ route('admin.reports.pdf') }}" class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Export PDF</a></div>@endif
</div>
<form method="GET" action="{{ auth()->user()->role === 'admin' ? route('admin.reports') : route('staff.reports') }}" class="mb-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-[1fr_180px_180px_auto_auto] dark:border-slate-800 dark:bg-slate-900">
    <select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
        <option value="">All statuses</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        <option value="approved" @selected(request('status') === 'approved')>In progress</option>
        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
    </select>
    <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <button class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Apply</button>
    @if (request()->hasAny(['status', 'start_date', 'end_date']))
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.reports') : route('staff.reports') }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700">Clear</a>
    @endif
</form>
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach (['pending' => 'Pending', 'approved' => 'In progress', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $key => $label)
        <article class="rounded-2xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p><p data-live-count="{{ $key === 'approved' ? 'in_progress' : $key }}" class="mt-2 text-3xl font-black">{{ $counts[$key] ?? 0 }}</p></article>
    @endforeach
</section>
<section class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><h3 class="text-lg font-bold">Reports by category</h3><div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse ($categories as $category => $total)<div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3 dark:bg-slate-800"><span class="text-sm font-medium capitalize">{{ str_replace('_', ' ', $category) }}</span><span class="font-bold">{{ $total }}</span></div>@empty<p class="text-sm text-slate-500">There are no complaints to report yet.</p>@endforelse</div></section>
@endsection
