@extends('layouts.public')

@section('title', 'A barangay that listens')

@section('content')
<header class="sticky top-0 z-20 border-b border-stone-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
        <a href="#" class="flex items-center gap-3">
            <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag official seal" class="size-11 shrink-0 object-contain">
            <span><strong class="block text-sm">BARANGAY POBLACION</strong><span class="text-xs text-slate-500 dark:text-slate-400">A community that cares</span></span>
        </a>
        <div class="hidden items-center gap-7 text-sm font-semibold text-slate-600 dark:text-slate-300 md:flex">
            <a class="hover:text-maroon" href="#home">Home</a><a class="hover:text-maroon" href="#about">About</a>
            <a class="hover:text-maroon" href="#features">Features</a><a class="hover:text-maroon" href="#contact">Contact</a>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.toggleAppearance()" aria-label="Toggle light and dark theme" class="grid size-10 place-items-center rounded-xl text-lg hover:bg-stone-100 dark:hover:bg-slate-800">◐</button>
            <a href="{{ route('login') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-maroon hover:bg-rose-50 dark:hover:bg-slate-800">Sign in</a>
            <a href="{{ route('register') }}" class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-maroon/15 hover:bg-maroon-dark">Get started</a>
        </div>
    </nav>
</header>

<main>
    <section id="home" class="relative isolate overflow-hidden bg-maroon text-white">
        <img src="{{ asset('images/mahaplag.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 size-full object-cover object-center">
        <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-maroon/85 via-maroon/65 to-maroon/50"></div>
        <div aria-hidden="true" class="absolute -right-24 -top-28 size-[30rem] rounded-full border border-white/10"></div>
        <div aria-hidden="true" class="absolute -right-2 top-16 size-[23rem] rounded-full border border-gold/30"></div>
        <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-5 py-20 sm:py-28 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-200"><span class="size-2 rounded-full bg-gold"></span> Your voice, our community</span>
                <h1 class="mt-7 max-w-3xl text-4xl font-black leading-[1.08] tracking-tight sm:text-6xl">Together, we make our barangay <span class="text-amber-300">better.</span></h1>
                <p class="mt-6 max-w-xl text-base leading-8 text-rose-100 sm:text-lg">A simple, secure way to report neighborhood concerns and follow every update from submission to resolution.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="rounded-xl bg-gold px-6 py-3.5 font-bold text-maroon-dark shadow-lg hover:bg-amber-300">Submit a concern <span aria-hidden="true">→</span></a>
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/30 px-6 py-3.5 font-bold text-white hover:bg-white/10">Sign in</a>
                </div>
                <p class="mt-5 text-xs text-rose-200">Already registered? Track your concern anytime from your resident portal.</p>
            </div>
            <div class="relative z-10 mx-auto w-full max-w-md">
                <div class="rounded-[2rem] border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur">
                    <div class="rounded-[1.5rem] bg-white p-6 text-slate-900 dark:bg-slate-900 dark:text-white">
                        <div class="flex items-center justify-between">
                            <div><p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Community pulse</p><h2 class="mt-1 text-xl font-bold">Your report matters</h2></div>
                            <span class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-2xl text-maroon">✳</span>
                        </div>
                        <div class="mt-7 space-y-4">
                            <div class="flex items-center gap-4 rounded-2xl bg-stone-50 p-4 dark:bg-slate-800"><span class="grid size-10 place-items-center rounded-xl bg-rose-100 font-bold text-maroon">01</span><div><p class="font-semibold">Tell us what's happening</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Share the details and location.</p></div></div>
                            <div class="flex items-center gap-4 rounded-2xl bg-stone-50 p-4 dark:bg-slate-800"><span class="grid size-10 place-items-center rounded-xl bg-amber-100 font-bold text-maroon">02</span><div><p class="font-semibold">Follow your updates</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">See progress as staff respond.</p></div></div>
                            <div class="flex items-center gap-4 rounded-2xl bg-stone-50 p-4 dark:bg-slate-800"><span class="grid size-10 place-items-center rounded-xl bg-emerald-100 font-bold text-emerald-800">03</span><div><p class="font-semibold">Help make change happen</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your feedback builds a better place.</p></div></div>
                        </div>
                    </div>
                    <p class="px-2 pb-1 pt-4 text-center text-xs font-medium text-rose-100">A safer, cleaner, more connected Poblacion.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="mx-auto grid max-w-7xl gap-12 px-5 py-20 md:grid-cols-2 md:items-center">
        <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-maroon">About the portal</p><h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">A better way to be heard.</h2><p class="mt-5 leading-8 text-slate-600 dark:text-slate-300">Barangay Poblacion's Complaint Management System brings residents and local responders together. Send a concern with clear details, keep your reference number, and receive timely updates in one place.</p><a href="{{ route('register') }}" class="mt-7 inline-flex font-bold text-maroon hover:text-maroon-dark">Create your resident account <span class="ml-2">→</span></a></div>
        <div class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900"><p class="text-sm font-bold">Built around your community</p><div class="mt-6 grid grid-cols-2 gap-4"><div class="rounded-2xl bg-rose-50 p-5 dark:bg-slate-800"><p class="text-2xl font-black text-maroon">01</p><p class="mt-2 text-sm font-semibold">Easy reporting</p></div><div class="rounded-2xl bg-amber-50 p-5 dark:bg-slate-800"><p class="text-2xl font-black text-maroon">02</p><p class="mt-2 text-sm font-semibold">Clear updates</p></div><div class="rounded-2xl bg-emerald-50 p-5 dark:bg-slate-800"><p class="text-2xl font-black text-maroon">03</p><p class="mt-2 text-sm font-semibold">Accountable action</p></div><div class="rounded-2xl bg-stone-100 p-5 dark:bg-slate-800"><p class="text-2xl font-black text-maroon">04</p><p class="mt-2 text-sm font-semibold">Secure records</p></div></div></div>
    </section>

    <section id="features" class="bg-white py-20 dark:bg-slate-900">
        <div class="mx-auto max-w-7xl px-5"><div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-maroon">Designed for people</p><h2 class="mt-4 text-3xl font-black sm:text-4xl">Everything you need to move a concern forward.</h2></div>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ([['01', 'Simple complaint forms', 'Tell the barangay what happened, where, and attach supporting photos or videos.'], ['02', 'A clear status timeline', 'Know when your concern is pending, being handled, resolved, or needs more information.'], ['03', 'Updates as they happen', 'Receive in-app notifications and see the latest status without refreshing your portal.']] as [$number, $title, $description])
                    <article class="rounded-2xl border border-stone-200 p-6 dark:border-slate-700"><span class="grid size-11 place-items-center rounded-xl bg-rose-50 font-black text-maroon dark:bg-slate-800">{{ $number }}</span><h3 class="mt-5 text-lg font-bold">{{ $title }}</h3><p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $description }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="mx-auto max-w-7xl px-5 py-20">
        <div class="rounded-[2rem] bg-maroon px-7 py-10 text-white sm:px-12">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">We're here to help</p><h2 class="mt-3 text-3xl font-black">Let's care for Poblacion together.</h2><p class="mt-3 max-w-xl text-sm leading-7 text-rose-100">For urgent concerns, visit your barangay office. For other inquiries, contact the local team during office hours.</p>
                @if ($information)
                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-rose-100">
                        @if ($information->address)<span>{{ $information->address }}</span>@endif
                        @if ($information->phone)<a href="tel:{{ $information->phone }}" class="underline decoration-white/30 underline-offset-4">{{ $information->phone }}</a>@endif
                        @if ($information->email)<a href="mailto:{{ $information->email }}" class="underline decoration-white/30 underline-offset-4">{{ $information->email }}</a>@endif
                        @if ($information->office_hours)<span>{{ $information->office_hours }}</span>@endif
                    </div>
                @endif
            </div>
        </div>
    </section>
</main>
<footer class="border-t border-stone-200 px-5 py-7 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">© {{ now()->year }} Barangay Poblacion · Serving our community together</footer>
@endsection
