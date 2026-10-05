@extends('layouts.public')

@section('title', 'Confirm your password')

@section('content')
<div class="flex min-h-screen items-center justify-center px-5 py-12">
    <div class="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-3xl font-black">Confirm your password</h1><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">For your security, please confirm your current password before continuing.</p>
        <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-7 space-y-4">@csrf
            <div><label for="password" class="text-sm font-semibold">Current password</label><input id="password" name="password" type="password" required autocomplete="current-password" autofocus class="mt-2 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <button class="w-full rounded-xl bg-maroon px-4 py-3 font-bold text-white hover:bg-maroon-dark">Confirm password</button>
        </form>
    </div>
</div>
@endsection
