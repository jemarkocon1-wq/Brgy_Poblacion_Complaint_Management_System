@extends('layouts.app')

@section('title', __('Summons service receipt').' · '.$service->complaint->reference)
@section('heading', __('Summons service receipt'))

@section('content')
    @php
        $resultLabel = match ($service->result) {
            'served' => __('Served'),
            'refused' => __('Refused to sign'),
            default => __('Unable to serve'),
        };
    @endphp
    <div class="mb-5 flex items-center justify-between gap-3 print:hidden">
        <a href="{{ route(auth()->user()->role.'.complaints.show', $service->complaint) }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← {{ __('Back to complaint') }}</a>
        <button type="button" onclick="window.print()" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white">{{ __('Print service receipt') }}</button>
    </div>
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
            body * { visibility: hidden !important; }
            #service-print, #service-print * { visibility: visible !important; }
            #service-print { position: absolute; inset: 0; width: 100%; max-width: none; border: 0; padding: 0; }
        }
    </style>
    <article id="service-print" class="mx-auto max-w-2xl rounded-2xl border border-stone-300 bg-white p-8 text-slate-900 shadow-sm">
        <header class="border-b-2 border-slate-900 pb-5 text-center">
            <p class="text-xs font-semibold uppercase">{{ __('Republic of the Philippines') }}</p>
            <p class="text-xs font-semibold uppercase">{{ __('Province of Leyte') }} · {{ __('Municipality of Mahaplag') }}</p>
            <h1 class="mt-2 text-lg font-black uppercase">{{ __('Barangay Poblacion') }}</h1>
            <p class="mt-1 text-sm font-semibold">{{ __('Summons service acknowledgment') }}</p>
        </header>
        <div class="mt-7 space-y-4 text-sm">
            <p><span class="font-semibold">{{ __('Case reference') }}:</span> {{ $service->complaint->reference }}</p>
            <p><span class="font-semibold">{{ __('Respondent') }}:</span> {{ $service->recipient_name ?? __('Not reached') }}</p>
            <p><span class="font-semibold">{{ __('Service date') }}:</span> {{ $service->served_at->format('F j, Y · g:i A') }}</p>
            <p><span class="font-semibold">{{ __('Service result') }}:</span> {{ $resultLabel }}</p>
            <p><span class="font-semibold">{{ __('Served by') }}:</span> {{ $service->server_name }}</p>
            <p><span class="font-semibold">{{ __('Signer') }}:</span> {{ $service->signer_name ?? __('No signature') }} ({{ ucfirst($service->signer_type) }})</p>
            @if ($service->refusal_response)
                <div><p class="font-semibold">{{ __('Refusal response') }}</p><p class="mt-1 whitespace-pre-line">{{ $service->refusal_response }}</p></div>
            @endif
        </div>
        <div class="mt-16 grid gap-10 sm:grid-cols-2">
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Respondent / recipient signature') }}</div>
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Server signature') }}</div>
        </div>
        <p class="mt-8 text-xs text-slate-500">{{ __('Recorded by') }}: {{ $service->recordedBy?->name ?? '—' }}</p>
    </article>
@endsection
