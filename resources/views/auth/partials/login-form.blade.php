<form method="POST" action="{{ $loginUrl }}" class="mt-8 space-y-5">
    @csrf
    <div>
        <label for="email" class="text-sm font-semibold">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required class="mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Enter your email address">
        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div x-data="{ visible: false }">
        <div class="flex items-center justify-between">
            <label for="password" class="text-sm font-semibold">Password</label>
            @if ($role === 'resident')
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-maroon hover:underline">Forgot password?</a>
            @endif
        </div>
        <div class="relative mt-2">
            <input id="password" name="password" :type="visible ? 'text' : 'password'" autocomplete="current-password" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" placeholder="Enter your password">
            <button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'" title="Show or hide password" class="absolute inset-y-0 right-3 text-lg text-slate-500">◉</button>
        </div>
        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <input name="remember" type="checkbox" value="1" class="size-4 rounded border-stone-300 accent-maroon">
        Remember me
    </label>
    <button class="w-full rounded-xl bg-maroon px-4 py-3.5 font-bold text-white shadow-lg shadow-maroon/15 hover:bg-maroon-dark">
        Sign in
    </button>
</form>
