@extends('layouts.app')

@section('title', 'All complaints')
@section('heading', 'All complaints')

@section('content')
<div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">Review reports and monitor the barangay response pipeline.</p><h2 class="mt-1 text-2xl font-black">Complaint management</h2></div>
<form method="GET" action="{{ route('admin.complaints.index') }}" role="search" class="mb-4 flex flex-col gap-2 sm:flex-row">
    <label for="admin-complaint-search" class="sr-only">Search complaints by title, reference, or resident name</label>
    <input id="admin-complaint-search" type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Search by title, reference, or resident name" class="min-w-0 flex-1 rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900">
    <button type="submit" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Search</button>
    @if ($search !== '')
        <a href="{{ route('admin.complaints.index') }}" class="rounded-xl border border-stone-300 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-stone-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Clear</a>
    @endif
</form>
@include('partials.complaint-table', ['complaints' => $complaints])
@endsection
