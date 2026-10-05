@extends('layouts.public')

@section('title', 'Create a resident account')

@section('content')
<div class="flex min-h-screen items-center justify-center px-5 py-10">
    <div class="w-full max-w-lg">
        <div class="mb-7 flex items-center justify-between"><a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to home</a><button type="button" onclick="window.toggleAppearance()" aria-label="Toggle light and dark theme" class="grid size-10 place-items-center rounded-xl border border-stone-200 text-lg dark:border-slate-700">◐</button></div>
        <div class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-slate-900/5 sm:p-9 dark:border-slate-800 dark:bg-slate-900">
            <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-12 object-contain">
            <h1 class="mt-6 text-3xl font-black">Join your community</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Create a resident account to submit and track barangay concerns.</p>
            <form method="POST" action="{{ route('register.store') }}" class="mt-7 space-y-4">
                @csrf
                <div><label class="text-sm font-semibold" for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700">@error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-semibold" for="email">Gmail address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@gmail.com" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700"><p class="mt-1 text-xs text-slate-500">Resident accounts must use an @gmail.com address.</p>@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-semibold" for="phone">Phone number</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700">@error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div x-data="{ visible: false }"><label class="text-sm font-semibold" for="password">Password</label><div class="relative mt-1.5"><input id="password" name="password" :type="visible ? 'text' : 'password'" required autocomplete="new-password" class="w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 pr-14 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700"><button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'" title="Show or hide password" class="absolute inset-y-0 right-3 text-lg text-slate-500">◉</button></div>@error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div x-data="{ visible: false }"><label class="text-sm font-semibold" for="password_confirmation">Confirm password</label><div class="relative mt-1.5"><input id="password_confirmation" name="password_confirmation" :type="visible ? 'text' : 'password'" required autocomplete="new-password" class="w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 pr-14 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700"><button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'" title="Show or hide password" class="absolute inset-y-0 right-3 text-lg text-slate-500">◉</button></div></div>
                <button class="w-full rounded-xl bg-maroon px-4 py-3.5 font-bold text-white hover:bg-maroon-dark">Create resident account</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-300">Already registered? <a href="{{ route('login') }}" class="font-bold text-maroon hover:underline">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
