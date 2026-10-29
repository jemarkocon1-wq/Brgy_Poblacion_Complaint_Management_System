@extends('layouts.app')

@section('title', __('Receipt acknowledgment').' · '.$complaint->reference)
@section('heading', __('Receipt acknowledgment'))

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3 print:hidden">
        <a href="{{ url()->previous() }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← {{ __('Back') }}</a>
        <button type="button" onclick="window.print()" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white">{{ __('Print receipt') }}</button>
    </div>
    <style>
        @media print {
            @page { size: A4; margin: 15mm; }
            body * { visibility: hidden !important; }
            #receipt-print, #receipt-print * { visibility: visible !important; }
            #receipt-print { position: absolute; inset: 0; width: 100%; max-width: none; border: 0; padding: 0; }
        }
    </style>
    <article id="receipt-print" class="mx-auto max-w-2xl rounded-2xl border border-stone-300 bg-white p-8 text-slate-900 shadow-sm">
        <header class="border-b-2 border-slate-900 pb-5 text-center">
            <p class="text-xs font-semibold uppercase">{{ __('Republic of the Philippines') }}</p>
            <p class="text-xs font-semibold uppercase">{{ __('Province of Leyte') }} · {{ __('Municipality of Mahaplag') }}</p>
            <h1 class="mt-2 text-lg font-black uppercase">{{ __('Barangay Poblacion') }}</h1>
            <p class="mt-1 text-sm font-semibold">{{ __('Receipt submission acknowledgment') }}</p>
        </header>
        <div class="mt-7 space-y-4 text-sm">
            <p><span class="font-semibold">{{ __('Case reference') }}:</span> {{ $complaint->reference }}</p>
            <p><span class="font-semibold">{{ __('Complainant') }}:</span> {{ $complaint->resident->name }}</p>
            <p><span class="font-semibold">{{ __('Date submitted') }}:</span> {{ $complaint->receipt_uploaded_at->format('F j, Y · g:i A') }}</p>
            <p><span class="font-semibold">{{ __('Confirmation status') }}:</span> {{ $complaint->receipt_confirmed_at ? __('Confirmed') : __('Awaiting staff confirmation') }}</p>
            @if ($complaint->receipt_confirmed_at)
                <p><span class="font-semibold">{{ __('Confirmed on') }}:</span> {{ $complaint->receipt_confirmed_at->format('F j, Y · g:i A') }}</p>
            @endif
        </div>
        <div class="mt-16 grid gap-10 sm:grid-cols-2">
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Complainant signature') }}</div>
            <div class="border-t border-slate-700 pt-2 text-center text-sm">{{ __('Barangay staff signature') }}</div>
        </div>
        <p class="mt-8 text-xs text-slate-500">{{ __('This printout acknowledges the status of the uploaded receipt proof. The original proof remains restricted to authorized barangay staff and administrators.') }}</p>
    </article>
@endsection
