@extends('layouts.app')

@section('title', !empty($assignedOnly) ? 'Assigned to me' : 'All complaints')
@section('heading', !empty($assignedOnly) ? 'Assigned to me' : 'All complaints')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm text-slate-500 dark:text-slate-400">Review, assign, and update resident concerns.</p><h2 class="mt-1 text-2xl font-black">{{ !empty($assignedOnly) ? 'Your assigned cases' : 'Complaint inbox' }}</h2></div>
    @if (empty($assignedOnly))<form method="GET" class="flex flex-wrap gap-2"><input name="search" value="{{ request('search') }}" placeholder="Reference or title" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"><select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">All statuses</option>@foreach (['pending', 'approved', 'rejected', 'resolved'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status === 'approved' ? 'In Progress' : $status) }}</option>@endforeach</select><button class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Filter</button></form>@endif
</div>
@include('partials.complaint-table', ['complaints' => $complaints])
@endsection
