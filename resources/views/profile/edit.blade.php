@extends('layouts.app')

@section('title', 'Profile and settings')
@section('heading', 'Profile and settings')

@section('content')
@php
    $role = auth()->user()->role;
    $profileRoute = $role === 'admin' ? 'admin.settings.update' : ($role === 'staff' ? 'staff.settings.update' : 'resident.profile.update');
@endphp
<div class="max-w-3xl">
    <div class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-widest text-maroon">Personal information</p><h2 class="mt-2 text-2xl font-black">Your profile</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Keep your account details up to date.</p>
        <form method="POST" action="{{ route($profileRoute) }}" class="mt-7 space-y-5">@csrf @method('PATCH')
            <div><label for="name" class="text-sm font-semibold">Full name</label><input id="name" name="name" value="{{ old('name', $profile->name) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="email" class="text-sm font-semibold">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $profile->email) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="phone" class="text-sm font-semibold">Phone number</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $profile->phone) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <button class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Save profile</button>
        </form>
    </div>
    <div class="mt-5 rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><div class="flex items-center justify-between gap-5"><div><h3 class="font-bold">Appearance</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose a comfortable color theme. Your choice is saved to this device and your account.</p></div><button type="button" onclick="window.toggleAppearance()" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-700">Toggle theme ◐</button></div></div>
</div>
@endsection
