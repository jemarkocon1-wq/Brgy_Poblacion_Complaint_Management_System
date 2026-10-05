@extends('layouts.app')

@section('title', 'System activity')
@section('heading', 'System activity')

@section('content')
<div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">A chronological record of complaint and account changes.</p><h2 class="mt-1 text-2xl font-black">Activity log</h2></div>
<form method="GET" action="{{ route('admin.activity') }}" class="mb-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-[1fr_180px_auto_auto] dark:border-slate-800 dark:bg-slate-900">
    <input name="search" value="{{ request('search') }}" placeholder="Search action details or actor" class="w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <select name="action" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
        <option value="">All actions</option>
        @foreach ($actions as $action)
            <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('.', ' ', ucfirst($action)) }}</option>
        @endforeach
    </select>
    <button class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Filter</button>
    @if (request()->hasAny(['action', 'search']))
        <a href="{{ route('admin.activity') }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700">Clear</a>
    @endif
</form>
<div class="rounded-2xl border border-stone-200 bg-white px-5 dark:border-slate-800 dark:bg-slate-900">
    @forelse ($activities as $activity)
        <article class="flex gap-4 border-b border-stone-100 py-5 last:border-0 dark:border-slate-800"><span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-sm font-black text-maroon dark:bg-slate-800">↗</span><div class="min-w-0 flex-1"><div class="flex flex-wrap justify-between gap-2"><p class="text-sm font-semibold">{{ $activity->description ?: str_replace('.', ' ', ucfirst($activity->action)) }}</p><time class="text-xs text-slate-500">{{ $activity->created_at->format('M j, Y · g:i A') }}</time></div><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $activity->actor?->name ?? 'System' }}@if($activity->complaint) · <a href="{{ route('admin.complaints.show', $activity->complaint) }}" class="font-semibold text-maroon hover:underline">{{ $activity->complaint->reference }}</a>@endif</p></div></article>
    @empty
        <p class="py-12 text-center text-sm text-slate-500">No system activity has been recorded.</p>
    @endforelse
    <div class="py-4">{{ $activities->links() }}</div>
</div>
@endsection
