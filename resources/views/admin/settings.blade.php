@extends('layouts.app')

@section('title', 'Admin settings')
@section('heading', 'Admin settings')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
    <section class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-widest text-maroon">Account preferences</p>
        <h2 class="mt-2 text-2xl font-black">Update administrator profile</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Keep your account details current and ensure the barangay office contact information remains accurate.</p>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-7 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="text-sm font-semibold">Full name</label>
                <input id="name" name="name" value="{{ old('name', $profile->name) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="text-sm font-semibold">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $profile->email) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="text-sm font-semibold">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $profile->phone) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Save settings</button>
        </form>
    </section>

    <aside class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold">Appearance</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Save your preferred interface theme.</p>
                </div>
                <button type="button" onclick="window.toggleAppearance()" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-700">Toggle theme</button>
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="font-bold">Quick links</h3>
            <div class="mt-4 space-y-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="block rounded-xl bg-stone-50 px-3 py-2 text-slate-700 transition hover:bg-stone-100 dark:bg-slate-800 dark:text-slate-200">Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="block rounded-xl bg-stone-50 px-3 py-2 text-slate-700 transition hover:bg-stone-100 dark:bg-slate-800 dark:text-slate-200">Manage users</a>
                <a href="{{ route('admin.reports') }}" class="block rounded-xl bg-stone-50 px-3 py-2 text-slate-700 transition hover:bg-stone-100 dark:bg-slate-800 dark:text-slate-200">Reports</a>
                <a href="{{ route('admin.information') }}" class="block rounded-xl bg-stone-50 px-3 py-2 text-slate-700 transition hover:bg-stone-100 dark:bg-slate-800 dark:text-slate-200">Barangay information</a>
            </div>
        </div>
    </aside>
</div>
@endsection
