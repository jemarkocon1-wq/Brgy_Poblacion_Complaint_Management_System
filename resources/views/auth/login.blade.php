@extends('layouts.public')

@section('title', 'Welcome back')

@section('content')
<div class="grid min-h-screen lg:grid-cols-[1fr_1fr]">
    <section class="relative hidden overflow-hidden bg-maroon bg-cover bg-center p-12 text-white lg:flex lg:flex-col lg:justify-between" style="background-image: linear-gradient(rgba(15, 23, 42, 0.38), rgba(15, 23, 42, 0.38)), url('{{ asset('images/allm.png') }}')">
        <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3"><img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-12 shrink-0 object-contain"><span><strong class="block text-sm">BARANGAY POBLACION</strong><span class="text-xs text-rose-200">Complaint Management System</span></span></a>
        <div class="relative z-10 max-w-xl pb-12"><p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Community portal</p><h1 class="mt-5 text-5xl font-black leading-tight">Good things happen when we work <span class="text-amber-300">together.</span></h1><p class="mt-5 max-w-lg leading-8 text-rose-100">Securely connect with your barangay and keep up with community concerns.</p></div>
        <div class="absolute -bottom-40 -right-28 size-[34rem] rounded-full border border-white/10"></div><div class="absolute bottom-16 right-16 size-64 rounded-full border border-gold/30"></div>
        <p class="relative z-10 text-xs text-rose-200">A safer, cleaner, more connected Poblacion.</p>
    </section>
    <section class="flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to home</a>
                <button type="button" onclick="window.toggleAppearance()" aria-label="Toggle light and dark theme" class="grid size-10 place-items-center rounded-xl border border-stone-200 text-lg dark:border-slate-700">◐</button>
            </div>
            <h2 class="text-3xl font-black">Welcome back</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Sign in to access your account, manage community concerns, and follow their progress.</p>
            @if (session('status'))<div class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>@endif
            @if (session('error'))<div class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ session('error') }}</div>@endif
            @include('auth.partials.login-form', ['role' => 'resident', 'loginUrl' => $loginUrl])
            <p class="mt-7 text-center text-sm text-slate-600 dark:text-slate-300">New resident? <a href="{{ route('register') }}" class="font-bold text-maroon hover:underline">Create an account</a></p>
            <p class="mt-10 text-center text-xs text-slate-400">Your account is protected by secure authentication.</p>
        </div>
    </section>
</div>
@endsection
