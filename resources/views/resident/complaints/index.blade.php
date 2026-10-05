@extends('layouts.app')

@section('title', 'My complaints')
@section('heading', 'My complaints')

@section('content')
<div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-sm text-slate-500 dark:text-slate-400">Every report you have shared with the barangay.</p><h2 class="mt-1 text-2xl font-black">Your complaint history</h2></div><a href="{{ route('resident.complaints.create') }}" class="rounded-xl bg-maroon px-5 py-3 text-center text-sm font-bold text-white hover:bg-maroon-dark">＋ New complaint</a></div>
@include('partials.complaint-table', ['complaints' => $complaints])
@endsection
