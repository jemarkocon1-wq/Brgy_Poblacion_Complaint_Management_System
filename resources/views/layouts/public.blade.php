<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => $appearance === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Barangay Poblacion') · Complaint Management</title>
    <script>
        (() => {
            const appearance = localStorage.getItem('appearance') || @js($appearance);
            document.documentElement.classList.toggle('dark', appearance === 'dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    @yield('content')
</body>
</html>
