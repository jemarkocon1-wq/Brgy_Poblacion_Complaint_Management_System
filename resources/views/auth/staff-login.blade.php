@extends('layouts.public')

@section('title', 'Staff sign in')

@section('content')
<main class="grid min-h-screen bg-stone-100 dark:bg-slate-950 lg:grid-cols-[0.9fr_1.1fr]">
    <section class="relative flex min-h-72 flex-col justify-between overflow-hidden bg-maroon bg-cover bg-center p-8 text-white sm:p-12 lg:min-h-screen" style="background-image: linear-gradient(rgba(67, 10, 27, 0.88), rgba(67, 10, 27, 0.78)), url('{{ asset('images/allm.png') }}')">
        <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
            <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-12 object-contain">
            <span><strong class="block text-sm">BARANGAY POBLACION</strong><span class="text-xs text-rose-200">Complaint Management System</span></span>
        </a>
        <div class="relative z-10 max-w-xl py-12">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Official staff access</p>
            <h1 class="mt-5 text-4xl font-black leading-tight sm:text-5xl">Serve the community with confidence.</h1>
            <p class="mt-5 max-w-lg leading-8 text-rose-100">Review complaints, respond to residents, and keep barangay services moving.</p>
        </div>
        <p class="relative z-10 text-xs text-rose-200">Authorized Barangay Poblacion personnel only.</p>
        <div aria-hidden="true" class="absolute -bottom-40 -right-28 size-[34rem] rounded-full border border-white/10"></div>
    </section>

    <section class="flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to home</a>
            <div class="mt-10 rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 sm:p-9">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-maroon">Staff workspace</p>
                <h2 class="mt-3 text-3xl font-black">Staff workspace sign in</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Sign in with your official staff account to access assigned work.</p>
                @if (session('status'))<div class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>@endif
                @if (session('error'))<div class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ session('error') }}</div>@endif
                @include('auth.partials.login-form', ['role' => 'staff', 'loginUrl' => $loginUrl])
            </div>
            <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-300">Looking for resident access? <a href="{{ route('login') }}" class="font-bold text-maroon hover:underline">Resident sign in</a></p>
        </div>
    </section>
</main>
@endsection
