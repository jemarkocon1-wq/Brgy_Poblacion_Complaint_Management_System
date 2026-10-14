@extends('layouts.app')

@section('title', 'Resident dashboard')
@section('heading', 'Your resident dashboard')

@section('content')
<section class="relative isolate flex flex-col justify-between gap-5 overflow-hidden rounded-3xl bg-maroon px-6 py-7 text-white sm:flex-row sm:items-center sm:px-8">
    <img src="{{ asset('images/crossing.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 -z-20 size-full object-cover object-center">
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-maroon/90 via-maroon/75 to-maroon/60"></div>
    <div class="relative"><p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-200">Welcome, {{ auth('resident')->user()->name }}</p><h2 class="mt-2 text-2xl font-black sm:text-3xl">Your voice helps Poblacion thrive.</h2><p class="mt-2 text-sm text-rose-100">Submit a concern or check where your reports stand.</p></div>
    <a href="{{ route('resident.complaints.create') }}" class="relative inline-flex shrink-0 items-center justify-center rounded-xl bg-gold px-5 py-3 font-bold text-maroon-dark hover:bg-amber-300">＋ Submit a complaint</a>
</section>

<section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['total', 'Total reports', 'All concerns submitted'], ['pending', 'Pending review', 'Awaiting the barangay team'], ['in_progress', 'In progress', 'Being handled by staff'], ['resolved', 'Resolved', 'Concerns addressed']] as [$key, $label, $hint])
        <article class="rounded-2xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $label }}</p><p data-live-count="{{ $key }}" class="mt-3 text-3xl font-black">{{ $counts[$key] }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p></article>
    @endforeach
</section>

<section class="mt-8">
    <div class="mb-4 flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-maroon">Your activity</p><h2 class="mt-1 text-xl font-black">Recent complaints</h2></div><a href="{{ route('resident.complaints.index') }}" class="text-sm font-bold text-maroon hover:underline">View all</a></div>
    <form method="GET" action="{{ route('resident.dashboard') }}" role="search" class="mb-4 flex flex-col gap-2 sm:flex-row">
        <label for="complaint-search" class="sr-only">Search your complaints by title or reference</label>
        <input id="complaint-search" type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Search by title or reference (e.g. BRGY-261010-PY8JRR)" class="min-w-0 flex-1 rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900">
        <button type="submit" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Search</button>
        @if ($search !== '')
            <a href="{{ route('resident.dashboard') }}" class="rounded-xl border border-stone-300 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-stone-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Clear</a>
        @endif
    </form>
    @if ($search !== '' && $recentComplaints->isEmpty())
        <div class="rounded-2xl border border-stone-200 bg-white px-5 py-10 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">No complaints found for "{{ $search }}". Try another title or reference.</div>
    @else
        @include('partials.complaint-table', ['complaints' => $recentComplaints])
    @endif
</section>
@endsection
