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
    @include('partials.complaint-table', ['complaints' => $recentComplaints])
</section>
@endsection
