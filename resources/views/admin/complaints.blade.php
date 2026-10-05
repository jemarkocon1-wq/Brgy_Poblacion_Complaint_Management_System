@extends('layouts.app')

@section('title', 'All complaints')
@section('heading', 'All complaints')

@section('content')
<div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">Review reports and monitor the barangay response pipeline.</p><h2 class="mt-1 text-2xl font-black">Complaint management</h2></div>
@include('partials.complaint-table', ['complaints' => $complaints])
@endsection
