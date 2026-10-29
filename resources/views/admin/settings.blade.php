@extends('layouts.app')

@section('title', 'Admin settings')
@section('heading', 'Admin settings')

@section('content')
@php
    $avatarPreview = $profile->avatar_path ? asset('storage/'.$profile->avatar_path) : null;
@endphp
<div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
    <section class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-widest text-maroon">Account preferences</p>
        <h2 class="mt-2 text-2xl font-black">Update administrator profile</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Keep your account details current and ensure the barangay office contact information remains accurate.</p>

        <div class="mt-6 flex items-center gap-4 rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="grid size-16 place-items-center overflow-hidden rounded-full border border-stone-200 bg-maroon/10 text-lg font-black text-maroon dark:border-slate-600 dark:bg-slate-700">
                @if ($avatarPreview)
                    <img src="{{ $avatarPreview }}" alt="{{ $profile->name }} profile picture" class="size-full object-cover">
                @else
                    <span>{{ strtoupper(substr($profile->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold">{{ $profile->name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Administrator</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-7 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="avatar" class="text-sm font-semibold">Profile picture</label>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1.5 block w-full rounded-xl border border-stone-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-rose-50 file:px-4 file:py-3 file:font-semibold file:text-maroon dark:border-slate-700 dark:file:bg-slate-800">
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">PNG, JPG, or WebP up to 2 MB.</p>
                @error('avatar')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div id="avatar-preview" class="hidden overflow-hidden rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-3 dark:border-slate-700 dark:bg-slate-800/40"></div>

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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('avatar-preview');

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function (event) {
            const [file] = event.target.files ?? [];
            preview.innerHTML = '';
            preview.classList.add('hidden');

            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            const previewUrl = URL.createObjectURL(file);
            const img = document.createElement('img');
            img.src = previewUrl;
            img.alt = 'Selected profile picture preview';
            img.className = 'h-32 w-32 rounded-full object-cover border border-stone-200 dark:border-slate-700';

            const label = document.createElement('p');
            label.textContent = 'Selected photo preview';
            label.className = 'mb-3 text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400';

            preview.appendChild(label);
            preview.appendChild(img);
            preview.classList.remove('hidden');
        });
    });
</script>
@endsection
