@php
    $user = auth()->user();
    $role = $user?->role ?? 'resident';
    $links = match ($role) {
        'admin' => [
            ['Overview', 'admin.dashboard'],
            ['Complaints', 'admin.complaints.index'],
            ['Users', 'admin.users.index'],
            ['Reports', 'admin.reports'],
            ['Barangay information', 'admin.information'],
            ['System activity', 'admin.activity'],
            ['Settings', 'admin.settings'],
        ],
        'staff' => [
            ['Overview', 'staff.dashboard'],
            ['All complaints', 'staff.complaints.index'],
            ['Assigned to me', 'staff.assigned'],
            ['Residents', 'staff.residents.index'],
            ['Reports', 'staff.reports'],
            ['Settings', 'staff.settings'],
        ],
        default => [
            ['Overview', 'resident.dashboard'],
            ['My complaints', 'resident.complaints.index'],
            ['Submit a complaint', 'resident.complaints.create'],
            ['My profile', 'resident.profile.edit'],
        ],
    };
    $logoutRoute = match ($role) {
        'admin' => 'admin.logout',
        'staff' => 'staff.logout',
        default => 'logout',
    };
    $notifications = $user?->unreadNotifications()->latest()->get() ?? collect();
    $unreadCount = $user?->unreadNotifications()->count() ?? 0;
    $dashboardRoute = match ($role) {
        'admin' => 'admin.dashboard',
        'staff' => 'staff.dashboard',
        default => 'resident.dashboard',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => $appearance === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Barangay Poblacion</title>
    <script>
        (() => {
            const appearance = localStorage.getItem('appearance') || @js($appearance);
            document.documentElement.classList.toggle('dark', appearance === 'dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-stone-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100"
      data-role="{{ $role }}" data-user-id="{{ $user?->id }}" data-authenticated="true">
    <div class="min-h-screen lg:flex">
        <aside class="hidden w-72 shrink-0 flex-col border-r border-stone-200 bg-white px-5 py-6 dark:border-slate-800 dark:bg-slate-900 lg:flex">
            <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-3 px-2">
                <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-12 shrink-0 object-contain">
                <span>
                    <span class="block text-sm font-bold tracking-wide">BARANGAY POBLACION</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Complaint Management</span>
                </span>
            </a>
            <p class="mb-3 mt-10 px-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $role }} portal</p>
            <nav class="flex flex-1 flex-col gap-1">
                @foreach ($links as [$label, $routeName])
                    <a href="{{ route($routeName) }}"
                       @class([
                           'rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                           'bg-maroon text-white shadow-sm' => request()->routeIs($routeName),
                           'text-slate-600 hover:bg-stone-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs($routeName),
                       ])>
                        {{ __($label) }}
                    </a>
                @endforeach
            </nav>
            @if ($role === 'resident')
                <div class="mt-8 rounded-2xl bg-rose-50 p-4 dark:bg-slate-800">
                    <p class="text-sm font-semibold text-maroon dark:text-rose-200">Need assistance?</p>
                    <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">Barangay staff are ready to help with your concern.</p>
                    <a href="{{ route('home') }}#contact" class="mt-3 inline-block text-xs font-bold text-maroon dark:text-rose-200">Contact the barangay →</a>
                </div>
            @endif
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-stone-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
                <div class="flex h-[76px] items-center justify-between gap-4 px-4 sm:px-8">
                    <div class="flex items-center gap-3">
                        <a href="{{ route($dashboardRoute) }}" class="lg:hidden">
                            <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-10 object-contain">
                        </a>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __(ucfirst($role)) }} {{ __('workspace') }}</p>
                            <h1 class="text-sm font-bold sm:text-base">@yield('heading', 'Welcome back')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <form method="POST" action="{{ route('locale.update') }}">
                            @csrf
                            <label for="locale" class="sr-only">{{ __('Language') }}</label>
                            <select id="locale" name="locale" onchange="this.form.submit()" class="h-10 max-w-28 rounded-xl border border-stone-200 bg-white px-2 text-xs font-semibold dark:border-slate-700 dark:bg-slate-900">
                                <option value="en" @selected(app()->getLocale() === 'en')>{{ __('English') }}</option>
                                <option value="fil" @selected(app()->getLocale() === 'fil')>{{ __('Filipino') }}</option>
                            </select>
                        </form>
                        <button type="button" onclick="window.toggleAppearance()" aria-label="Toggle light and dark theme"
                                class="grid size-10 place-items-center rounded-xl border border-stone-200 text-lg hover:bg-stone-100 dark:border-slate-700 dark:hover:bg-slate-800">◐</button>
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" aria-label="Notifications" :aria-expanded="open.toString()"
                                    class="relative grid size-10 place-items-center rounded-xl border border-stone-200 text-lg hover:bg-stone-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                ♧
                                <span data-notification-count @class(['absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-600 px-1 text-center text-[10px] font-bold text-white', 'hidden' => $unreadCount === 0])>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            </button>
                            <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 z-30 mt-3 flex max-h-[min(32rem,calc(100dvh-6rem))] w-80 max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900" aria-label="Unread notifications">
                                <div class="flex items-center justify-between gap-2 px-2 py-1">
                                    <p class="text-sm font-bold">Notifications <span class="font-normal text-slate-500">({{ $unreadCount }} unread)</span></p>
                                    @if ($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.read-all') }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold text-maroon hover:underline dark:text-rose-200">Read all</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="mt-2 min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain pr-1 [scrollbar-gutter:stable]">
                                    @forelse ($notifications as $notification)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="flex w-full items-start gap-2 rounded-xl bg-white px-2 py-2 text-left text-xs text-slate-600 transition hover:bg-stone-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                                <span aria-label="Unread" class="mt-1.5 size-2 shrink-0 rounded-full bg-rose-600"></span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block font-semibold text-slate-900 dark:text-white">{{ $notification->data['reference'] ?? 'Complaint update' }}</span>
                                                    <span class="mt-1 block">{{ $notification->data['message'] ?? 'There is a new complaint update.' }}</span>
                                                    <span class="mt-2 block font-bold text-maroon dark:text-rose-200">Mark as read · view details</span>
                                                </span>
                                            </button>
                                        </form>
                                    @empty
                                        <p class="px-2 py-3 text-xs text-slate-500">You're all caught up. Read notifications disappear from this list.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @php
                            $userAvatar = $user?->avatar_path ? asset('storage/'.$user->avatar_path) : null;
                        @endphp
                        @if ($userAvatar)
                            <img src="{{ $userAvatar }}" alt="{{ $user->name }} profile picture" class="size-11 rounded-full object-cover ring-2 ring-stone-200 dark:ring-slate-700">
                        @else
                            <div class="grid size-11 place-items-center rounded-full bg-maroon/10 text-sm font-black text-maroon ring-2 ring-stone-200 dark:ring-slate-700">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold">{{ $user->name }}</p>
                            <p class="text-xs capitalize text-slate-500 dark:text-slate-400">{{ $role }}</p>
                        </div>
                        <form method="POST" action="{{ route($logoutRoute) }}">
                            @csrf
                            <button class="rounded-xl px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-stone-100 dark:text-slate-300 dark:hover:bg-slate-800">Sign out</button>
                        </form>
                    </div>
                </div>
                <nav class="flex gap-2 overflow-x-auto px-4 pb-3 lg:hidden">
                    @foreach ($links as [$label, $routeName])
                            <a href="{{ route($routeName) }}" @class(['shrink-0 rounded-lg px-3 py-2 text-xs font-semibold', 'bg-maroon text-white' => request()->routeIs($routeName), 'bg-stone-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! request()->routeIs($routeName)])>{{ __($label) }}</a>
                    @endforeach
                </nav>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-8">
                <p data-appearance-error role="status" aria-live="polite" class="hidden mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-100"></p>
                @if (session('status'))
                    <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <div id="live-announcement" class="sr-only" aria-live="polite"></div>
    <div data-live-toasts class="fixed bottom-4 right-4 z-[100] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3" aria-live="polite"></div>
    @stack('scripts')
</body>
</html>
