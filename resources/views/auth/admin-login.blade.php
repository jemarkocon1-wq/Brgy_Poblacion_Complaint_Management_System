@extends('layouts.public')

@section('title', 'Staff and administrator sign in')

@section('content')
<main class="flex min-h-screen items-center justify-center bg-slate-950 px-5 py-12 text-white">
    <section class="w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-2xl shadow-black/30 sm:p-10">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-400 hover:text-white">← Back to home</a>
        <div class="mt-10 flex items-center gap-4">
            <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-14 object-contain">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-300">Restricted area</p>
                <p class="mt-1 text-sm font-semibold text-slate-300">Barangay Poblacion staff and administration</p>
            </div>
        </div>
        <h1 class="mt-8 text-3xl font-black">Staff &amp; Admin sign in</h1>
        <p class="mt-2 leading-7 text-slate-400">Sign in with your staff or administrator account. You will be directed to the workspace for your role.</p>
        @if (session('status'))<div class="mt-5 rounded-xl bg-emerald-950 p-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        @if (session('error'))<div class="mt-5 rounded-xl bg-rose-950 p-3 text-sm text-rose-200">{{ session('error') }}</div>@endif
        @include('auth.partials.login-form', ['role' => 'staff', 'loginUrl' => $loginUrl])
        <p class="mt-7 border-t border-slate-800 pt-5 text-center text-xs text-slate-500">Only authorized Barangay Poblacion staff and administrators may sign in here.</p>
    </section>
</main>
@endsection
