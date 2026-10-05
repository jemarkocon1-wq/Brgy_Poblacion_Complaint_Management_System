@extends('layouts.public')

@section('title', 'Reset your password')

@section('content')
<div class="flex min-h-screen items-center justify-center px-5 py-12">
    <div class="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Sign in</a>
        <h1 class="mt-7 text-3xl font-black">Forgot your password?</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Enter your Gmail address and we'll email you a secure reset link.</p>
        @if (session('status'))<p class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</p>@endif
        @if (session('error'))<p class="mt-5 rounded-xl bg-rose-50 p-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200">{{ session('error') }}</p>@endif
        <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-4">@csrf
            <div><label for="email" class="text-sm font-semibold">Gmail address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <button class="w-full rounded-xl bg-maroon px-4 py-3 font-bold text-white hover:bg-maroon-dark">Email password reset link</button>
        </form>
    </div>
</div>
@endsection
