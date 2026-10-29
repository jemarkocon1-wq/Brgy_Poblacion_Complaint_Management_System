@extends('layouts.app')

@section('title', __('Meeting minutes').' · '.$complaint->reference)
@section('heading', __('Meeting minutes'))

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3 print:hidden">
        <a href="{{ url()->previous() }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← {{ __('Back') }}</a>
        <button type="button" onclick="window.print()" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white">{{ __('Print minutes / agreement') }}</button>
    </div>
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
            body * { visibility: hidden !important; }
            #meeting-print, #meeting-print * { visibility: visible !important; }
            #meeting-print { position: absolute; inset: 0; width: 100%; max-width: none; border: 0; padding: 0; }
        }
    </style>
    <article id="meeting-print" class="mx-auto max-w-3xl rounded-2xl border border-stone-300 bg-white p-8 text-slate-900 shadow-sm">
        <header class="border-b-2 border-slate-900 pb-5 text-center">
            <p class="text-xs font-semibold uppercase">{{ __('Republic of the Philippines') }}</p>
            <p class="text-xs font-semibold uppercase">{{ __('Province of Leyte') }} · {{ __('Municipality of Mahaplag') }}</p>
            <h1 class="mt-2 text-lg font-black uppercase">{{ __('Barangay Poblacion') }}</h1>
            <p class="mt-1 text-sm font-semibold">{{ __('Minutes of mediation / hearing') }}</p>
        </header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <p><span class="font-semibold">{{ __('Case reference') }}:</span> {{ $complaint->reference }}</p>
            <p><span class="font-semibold">{{ __('Meeting type') }}:</span> {{ __(ucfirst($meeting->meeting_type)) }}</p>
            <p><span class="font-semibold">{{ __('Meeting date') }}:</span> {{ $meeting->meeting_date->format('F j, Y') }}</p>
            <p><span class="font-semibold">{{ __('Outcome') }}:</span> {{ __($meeting->outcome === 'amicable_settlement' ? 'Amicable settlement' : 'Unsettled') }}</p>
            <p class="sm:col-span-2"><span class="font-semibold">{{ __('Complainant') }}:</span> {{ $complaint->resident->name }}</p>
            @if ($meeting->participants)
                <p class="whitespace-pre-line sm:col-span-2"><span class="font-semibold">{{ __('Participants') }}:</span> {{ $meeting->participants }}</p>
            @endif
        </div>

        <section class="mt-7">
            <h2 class="font-bold">{{ __('Minutes') }}</h2>
            <p class="mt-2 whitespace-pre-line leading-7">{{ $meeting->minutes }}</p>
        </section>

        @if ($meeting->settlement_terms)
            <section class="mt-7 border-t border-slate-300 pt-5">
                <h2 class="font-bold">{{ __('Terms of amicable settlement') }}</h2>
                <p class="mt-2 whitespace-pre-line leading-7">{{ $meeting->settlement_terms }}</p>
            </section>
        @endif

        @if ($meeting->outcome === 'unsettled')
            <p class="mt-7 rounded-lg border border-slate-300 p-4 text-sm">
                {{ __('No amicable settlement was reached. This recorded outcome may be used by authorized barangay officials as a basis for the next appropriate action.') }}
            </p>
        @endif

        <div class="mt-16 grid gap-10 sm:grid-cols-2">
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Complainant signature') }}</div>
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Respondent signature') }}</div>
        </div>
        <p class="mt-10 text-right text-xs text-slate-500">{{ __('Recorded by') }}: {{ $meeting->recordedBy?->name ?? '—' }}</p>
    </article>
@endsection
