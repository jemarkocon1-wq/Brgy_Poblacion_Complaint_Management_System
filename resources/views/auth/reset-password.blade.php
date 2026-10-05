@extends('layouts.public')

@section('title', 'Choose a new password')

@section('content')
<div class="flex min-h-screen items-center justify-center px-5 py-12">
    <div class="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-3xl font-black">Choose a new password</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Use at least 8 characters and keep it somewhere safe.</p>
        <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-4">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div><label for="email" class="text-sm font-semibold">Gmail address</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="mt-2 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="password" class="text-sm font-semibold">New password</label><input id="password" name="password" type="password" required autocomplete="new-password" class="mt-2 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="password_confirmation" class="text-sm font-semibold">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-2 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700"></div>
            <button class="w-full rounded-xl bg-maroon px-4 py-3 font-bold text-white hover:bg-maroon-dark">Save new password</button>
        </form>
    </div>
</div>
@endsection
