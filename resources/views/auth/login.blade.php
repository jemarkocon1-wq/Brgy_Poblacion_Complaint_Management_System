@extends('layouts.public')

@php
    $titles = ['resident' => 'Welcome back', 'staff' => 'Official sign in', 'admin' => 'Administrator access'];
    $descriptions = ['resident' => 'Sign in to submit a concern and follow its progress.', 'staff' => 'Access your barangay complaint workspace.', 'admin' => 'Sign in to manage the complaint system.'];
@endphp

@section('title', $titles[$role])

@section('content')
<div class="grid min-h-screen lg:grid-cols-[1fr_1fr]">
    <section class="relative hidden overflow-hidden bg-maroon p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3"><img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-12 shrink-0 object-contain"><span><strong class="block text-sm">BARANGAY POBLACION</strong><span class="text-xs text-rose-200">Complaint Management System</span></span></a>
        <div class="relative z-10 max-w-xl pb-12"><p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">{{ ucfirst($role) }} portal</p><h1 class="mt-5 text-5xl font-black leading-tight">Good things happen when we work <span class="text-amber-300">together.</span></h1><p class="mt-5 max-w-lg leading-8 text-rose-100">Securely connect with your barangay and keep up with community concerns.</p></div>
        <div class="absolute -bottom-40 -right-28 size-[34rem] rounded-full border border-white/10"></div><div class="absolute bottom-16 right-16 size-64 rounded-full border border-gold/30"></div>
        <p class="relative z-10 text-xs text-rose-200">A safer, cleaner, more connected Poblacion.</p>
    </section>
    <section class="flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to home</a>
                <button type="button" onclick="window.toggleAppearance()" aria-label="Toggle light and dark theme" class="grid size-10 place-items-center rounded-xl border border-stone-200 text-lg dark:border-slate-700">◐</button>
            </div>
            <h2 class="text-3xl font-black">{{ $titles[$role] }}</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $descriptions[$role] }}</p>
            @if (session('status'))<div class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>@endif
            @if (session('error'))<div class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ session('error') }}</div>@endif
            <form method="POST" action="{{ $loginUrl }}" class="mt-8 space-y-5">
                @csrf
                <div><label for="email" class="text-sm font-semibold">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900" placeholder="{{ $role === 'resident' ? 'you@gmail.com' : 'name@barangay.gov.ph' }}">@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div x-data="{ visible: false }"><div class="flex items-center justify-between"><label for="password" class="text-sm font-semibold">Password</label>@if ($role === 'resident')<a href="{{ route('password.request') }}" class="text-xs font-semibold text-maroon hover:underline">Forgot password?</a>@endif</div><div class="relative mt-2"><input id="password" name="password" :type="visible ? 'text' : 'password'" autocomplete="current-password" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 pr-14 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900" placeholder="Enter your password"><button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'" title="Show or hide password" class="absolute inset-y-0 right-3 text-lg text-slate-500">◉</button></div>@error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"><input name="remember" type="checkbox" value="1" class="size-4 rounded border-stone-300 accent-maroon"> Remember me</label>
                <button class="w-full rounded-xl bg-maroon px-4 py-3.5 font-bold text-white shadow-lg shadow-maroon/15 hover:bg-maroon-dark">Sign in</button>
            </form>
            @if ($role === 'resident')
                <p class="mt-7 text-center text-sm text-slate-600 dark:text-slate-300">New to the portal? <a href="{{ route('register') }}" class="font-bold text-maroon hover:underline">Create an account</a></p>
            @endif
            <p class="mt-10 text-center text-xs text-slate-400">Your account is protected by secure authentication.</p>
        </div>
    </section>
</div>
@endsection
