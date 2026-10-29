@extends('layouts.app')

@section('title', 'Summons · '.$complaint->reference)
@section('heading', 'Prepare summons')

@push('head')
    <style>
        .summons-paper {
            box-sizing: border-box;
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 42px 48px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .summons-line {
            display: inline-block;
            min-width: 220px;
            border-bottom: 1px solid #374151;
            padding: 0 8px 2px;
            font-weight: 600;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: #fff !important;
                color: #111827 !important;
            }

            body > div > aside,
            body > div > div > header,
            .summons-actions,
            .summons-editor {
                display: none !important;
            }

            @if (! $editable)
                .resident-summons-view {
                    display: none !important;
                }
            @endif

            body > div,
            body > div > div,
            main {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .summons-paper {
                max-width: none;
                border: 0;
                padding: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="summons-actions mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route($role.'.complaints.show', $complaint) }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to complaint</a>
        @if ($complaint->summons_ready_at && $editable)
            <button type="button" onclick="window.print()" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Print summons</button>
        @endif
    </div>

    @if ($editable)
        <form method="POST" action="{{ route($role.'.complaints.summons.update', $complaint) }}" class="summons-editor mx-auto mb-6 max-w-3xl rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            @csrf
            <h2 class="text-xl font-black">Summons details</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Fill in the case number and hearing schedule. The complaint details below are inserted into the printable form.</p>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="summons_case_number" class="text-sm font-semibold">Barangay case number</label>
                    <input id="summons_case_number" name="summons_case_number" value="{{ old('summons_case_number', $complaint->summons_case_number) }}" placeholder="e.g. CV-24-2025" required maxlength="80" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_case_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="summons_for" class="text-sm font-semibold">For</label>
                    <input id="summons_for" name="summons_for" value="{{ old('summons_for', $complaint->summons_for ?? $complaint->case_subject ?? $complaint->title) }}" required maxlength="255" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_for')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="summons_hearing_date" class="text-sm font-semibold">Appearance date</label>
                    <input id="summons_hearing_date" name="summons_hearing_date" type="date" value="{{ old('summons_hearing_date', $complaint->summons_hearing_date?->format('Y-m-d')) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_hearing_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="summons_hearing_time" class="text-sm font-semibold">Appearance time</label>
                    <input id="summons_hearing_time" name="summons_hearing_time" type="time" value="{{ old('summons_hearing_time', $complaint->summons_hearing_time) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_hearing_time')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="summons_issued_date" class="text-sm font-semibold">Date issued</label>
                    <input id="summons_issued_date" name="summons_issued_date" type="date" value="{{ old('summons_issued_date', $complaint->summons_issued_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_issued_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="summons_signatory" class="text-sm font-semibold">Authorized signatory</label>
                    <input id="summons_signatory" name="summons_signatory" value="{{ old('summons_signatory', $complaint->summons_signatory) }}" placeholder="Name of Punong Barangay / Lupon Chairman" required maxlength="255" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">
                    @error('summons_signatory')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <button class="mt-5 rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">{{ $complaint->summons_ready_at ? 'Save changes' : 'Save and prepare summons' }}</button>
        </form>
    @endif

    @if ($complaint->summons_ready_at)
        <article @class(['summons-paper', 'resident-summons-view' => ! $editable])>
            <header class="border-b-2 border-slate-900 pb-5 text-center uppercase">
                <div class="flex items-center justify-center gap-4">
                    <img src="{{ asset('images/brgy_logo.png') }}" alt="" class="size-14 object-contain">
                    <div>
                        <p class="text-xs font-semibold">Republic of the Philippines</p>
                        <p class="text-xs font-semibold">Province of {{ $barangayInformation?->province ?: 'Leyte' }}</p>
                        <p class="text-xs font-semibold">Municipality of {{ $barangayInformation?->municipality ?: 'Mahaplag' }}</p>
                        <p class="text-xs font-semibold">{{ $barangayInformation?->name ?: 'Barangay Poblacion' }}</p>
                        <h1 class="mt-3 text-base font-black">Office of the Lupong Tagapamayapa</h1>
                    </div>
                </div>
            </header>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase">Complainant/s</p>
                    <p class="mt-2 border-b border-slate-700 pb-1 font-semibold">{{ $complaint->resident->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase">Barangay case no.</p>
                    <p class="mt-2 border-b border-slate-700 pb-1 font-semibold">{{ $complaint->summons_case_number }}</p>
                    <p class="mt-3 text-xs font-bold uppercase">For</p>
                    <p class="mt-1 border-b border-slate-700 pb-1 font-semibold">{{ $complaint->summons_for }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase">Respondent/s</p>
                    <p class="mt-2 whitespace-pre-line border-b border-slate-700 pb-1 font-semibold">{{ $complaint->respondents }}</p>
                </div>
            </div>

            <h2 class="my-7 text-center text-lg font-black tracking-[0.3em]">SUMMONS</h2>
            <p class="font-semibold uppercase">To:</p>
            <p class="mb-5 min-h-8 border-b border-slate-700 pb-1 font-semibold">{{ $complaint->respondents }}</p>
            <p class="leading-8">You are hereby summoned to appear before me in person, together with your witnesses, on the <span class="summons-line">{{ $complaint->summons_hearing_date->format('jS') }}</span> day of <span class="summons-line">{{ $complaint->summons_hearing_date->format('F Y') }}</span> at <span class="summons-line">{{ \Illuminate\Support\Carbon::createFromFormat('H:i', $complaint->summons_hearing_time)->format('g:i A') }}</span> o'clock in the morning/afternoon, then and there to answer to a complaint made before me, a copy of which is attached hereto, for mediation/conciliation of your dispute with the complainant/s.</p>
            <p class="mt-5 leading-8">You are hereby warned that if you refuse or willfully fail to appear in obedience to this summons, you may be barred from filing any counterclaim arising from said complaint.</p>
            <p class="mt-5 font-bold uppercase">Fail not or else punishment as for contempt of court.</p>
            <p class="mt-5">This <span class="summons-line">{{ $complaint->summons_issued_date->format('jS') }}</span> day of <span class="summons-line">{{ $complaint->summons_issued_date->format('F Y') }}</span>.</p>

            <div class="mt-16 text-center">
                <p class="mx-auto max-w-xs border-t border-slate-700 pt-2 font-bold uppercase">{{ $complaint->summons_signatory }}</p>
                <p class="text-xs">Punong Barangay/Lupon Chairman</p>
            </div>
        </article>
    @else
        <div class="mx-auto max-w-3xl rounded-2xl border border-dashed border-stone-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Save the summons details to generate the printable form.
        </div>
    @endif
@endsection
