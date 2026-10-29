@extends('layouts.app')

@section('title', 'Staff dashboard')
@section('heading', 'Good day, '.auth('staff')->user()->name)

@section('content')
<section class="relative isolate flex flex-col justify-between gap-5 overflow-hidden rounded-3xl bg-maroon px-6 py-7 text-white sm:flex-row sm:items-center sm:px-8">
    <img src="{{ asset('images/crossing.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 -z-20 size-full object-cover object-center">
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-maroon/90 via-maroon/75 to-maroon/60"></div>
    <div class="relative"><p class="text-xs font-bold uppercase tracking-widest text-amber-200">Barangay response desk</p><h2 class="mt-2 text-2xl font-black sm:text-3xl">Every report is a chance to help.</h2><p class="mt-2 text-sm text-rose-100">Review concerns, coordinate a response, and keep residents informed.</p></div>
    <a href="{{ route('staff.complaints.index') }}" class="relative rounded-xl bg-gold px-5 py-3 text-center text-sm font-bold text-maroon-dark hover:bg-amber-300">Review complaints →</a>
</section>
<section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
    @foreach ([['pending', 'New reports', 'Waiting for review'], ['in_progress', 'In progress', 'Currently being handled'], ['unsettled', 'Unsettled', 'No agreement reached'], ['resolved', 'Resolved', 'Resident confirmed resolved'], ['closed', 'Auto-closed', 'No staff response in three days'], ['assigned', 'Assigned to me', 'Your active case load']] as [$key, $label, $hint])
        <article class="rounded-2xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $label }}</p><p data-live-count="{{ $key }}" class="mt-3 text-3xl font-black">{{ $counts[$key] }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p></article>
    @endforeach
</section>
<section class="mt-8"><div class="mb-4 flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-maroon">Latest activity</p><h2 class="mt-1 text-xl font-black">Recent complaints</h2></div><a href="{{ route('staff.complaints.index') }}" class="text-sm font-bold text-maroon hover:underline">All complaints</a></div>@include('partials.complaint-table', ['complaints' => $recentComplaints])</section>
@endsection
