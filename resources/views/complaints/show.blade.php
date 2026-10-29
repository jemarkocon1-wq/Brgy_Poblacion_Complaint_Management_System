@extends('layouts.app')

@section('title', $complaint->reference)
@section('heading', 'Complaint details')

@section('content')
@php
    $role = auth()->user()->role;
    $baseRoute = $role === 'admin' ? 'admin.complaints.index' : ($role === 'staff' ? 'staff.complaints.index' : 'resident.complaints.index');
    $categoryLabel = match ($complaint->category) {
        'civil' => __('Civil'),
        'criminal_case' => __('Criminal case'),
        default => __(str_replace('_', ' ', ucfirst($complaint->category))),
    };
    $showPrintableComplaint = in_array($role, ['staff', 'admin'], true)
        && in_array($complaint->category, ['civil', 'criminal_case'], true);
@endphp
<div class="mb-5"><a href="{{ route($baseRoute) }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to complaints</a></div>
@if ($showPrintableComplaint)
    <div class="mb-5 flex justify-end print:hidden">
        <button type="button" onclick="window.print()" class="rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Print complaint</button>
    </div>
    <style>
        .complaint-paper {
            box-sizing: border-box;
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            padding: 36px 42px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            background: #fff;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .paper-header {
            border-bottom: 2px solid #111827;
            padding-bottom: 20px;
            text-transform: uppercase;
        }

        .paper-letterhead {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) 64px;
            align-items: center;
            gap: 12px;
            text-align: center;
        }

        .paper-parties {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 24px;
        }

        .paper-ruled {
            min-height: 128px;
            padding: 0 4px;
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 27px,
                #9ca3af 27px,
                #9ca3af 28px
            );
            line-height: 28px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .paper-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 40px;
            text-align: center;
        }

        .paper-bottom {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            align-items: end;
            column-gap: 18px;
            row-gap: 12px;
            margin-top: 24px;
            font-size: 11px;
        }

        .paper-signature {
            text-align: center;
            font-size: 10px;
        }

        .paper-signature-line {
            margin-top: 4px;
            border-bottom: 1px solid #64748b;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body * { visibility: hidden !important; }
            #complaint-print, #complaint-print * { visibility: visible !important; }
            #complaint-print {
                position: absolute;
                top: 0;
                left: 0;
                z-index: 99999;
                width: 190mm;
                max-width: none;
                margin: 0;
                border: 0;
                border-radius: 0;
                padding: 0;
                color: #111827 !important;
                background: #fff !important;
                box-shadow: none;
                font-size: 9.5px;
                line-height: 1.25;
            }

            #complaint-print > :not(:first-child) {
                margin-top: 3mm !important;
            }

            #complaint-print .paper-header {
                padding-bottom: 2.5mm;
            }

            #complaint-print .paper-letterhead {
                grid-template-columns: 14mm minmax(0, 1fr) 14mm;
                gap: 3mm;
            }

            #complaint-print .paper-letterhead img {
                width: 14mm;
                height: 14mm;
            }

            #complaint-print .paper-letterhead p,
            #complaint-print .paper-letterhead h2 {
                margin-top: 0 !important;
                font-size: 8.5px !important;
                line-height: 1.2;
            }

            #complaint-print .paper-letterhead p:last-child {
                margin-top: 1.5mm !important;
            }

            #complaint-print .paper-parties {
                margin-top: 4mm !important;
            }

            #complaint-print .paper-ruled {
                min-height: 24mm;
                background-size: 100% 5mm;
                line-height: 5mm;
            }

            #complaint-print .paper-bottom {
                margin-top: 4mm !important;
                column-gap: 5mm;
                row-gap: 3mm;
                font-size: 9px;
            }

            #complaint-print .paper-signature {
                font-size: 8.5px;
            }

            #complaint-print .paper-signature-line {
                margin-top: 1mm;
            }

            #complaint-print .paper-bottom > div:first-child p + p {
                margin-top: 5mm !important;
            }

            .paper-ruled {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            #complaint-print * {
                color: #111827 !important;
                border-color: #9ca3af !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }
        }
    </style>
@endif
<div class="grid gap-6 xl:grid-cols-[1fr_330px]">
    <div class="space-y-6">
        @if ($showPrintableComplaint)
            <section id="complaint-print" class="complaint-paper">
                <header class="paper-header">
                    <div class="paper-letterhead">
                        <img src="{{ asset('images/brgy_logo.png') }}" alt="Barangay Poblacion seal" class="size-14 object-contain">
                        <div>
                            <p class="text-xs font-semibold tracking-wider">Republic of the Philippines</p>
                            <p class="text-xs font-semibold tracking-wider">Province of Leyte</p>
                            <p class="text-xs font-semibold tracking-wider">Municipality of Mahaplag</p>
                            <h2 class="text-sm font-black tracking-wider">Barangay Poblacion</h2>
                            <p class="mt-3 text-xs font-bold tracking-widest">Office of the Lupong Tagapamayapa</p>
                        </div>
                        <img src="{{ asset('images/mahaplag-logo.png') }}" alt="Municipality of Mahaplag seal" class="size-14 object-contain">
                    </div>
                </header>

                <div class="paper-parties">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider">Complainant/s</p>
                        <p class="mt-1 text-sm font-semibold">{{ $complaint->resident->name }}</p>
                        @if ($complaint->resident->phone)
                            <p class="text-xs">{{ $complaint->resident->phone }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase tracking-wider">Barangay Case No.</p>
                        <p class="mt-1 text-sm font-bold">{{ $complaint->reference }}</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-wider">Category</p>
                        <p class="mt-1 text-sm">{{ $categoryLabel }}</p>
                    </div>
                </div>

                <div class="mt-5 border-y border-slate-400 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wider">Respondent/s</p>
                    <p class="mt-1 whitespace-pre-line text-sm">{{ $complaint->respondents ?: '—' }}</p>
                </div>
                <div class="mt-3">
                    <p class="text-xs font-semibold uppercase tracking-wider">For</p>
                    <p class="mt-1 text-sm font-semibold">{{ $complaint->case_subject ?: $complaint->title }}</p>
                    <p class="mt-1 text-xs">{{ $complaint->title }}</p>
                </div>

                <div class="mt-7 text-center">
                    <h3 class="text-lg font-black uppercase tracking-[0.35em]">Complaint</h3>
                </div>
                <p class="mt-4 text-sm leading-7">I/We hereby complain against the above-named respondent/s for the following:</p>
                @if ($complaint->category === 'criminal_case')
                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <p><span class="font-semibold">Alleged offense:</span> {{ $complaint->case_subject ?: '—' }}</p>
                        <p><span class="font-semibold">Date of incident:</span> {{ $complaint->incident_date?->format('F j, Y') ?? '—' }}</p>
                    </div>
                @endif
                <div class="paper-ruled mt-3 text-sm">{{ $complaint->description }}</div>
                @if ($complaint->category === 'civil')
                    <p class="mt-6 text-sm leading-7">THEREFORE, I/We pray that the following relief/s be granted in accordance with law and/or equity:</p>
                    <div class="paper-ruled mt-3 min-h-[84px] text-sm">{{ $complaint->relief_sought }}</div>
                @elseif ($complaint->witnesses)
                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-wider">Witnesses or supporting evidence</p>
                        <div class="paper-ruled mt-2 min-h-[84px] text-sm">{{ $complaint->witnesses }}</div>
                    </div>
                @endif

                <div class="paper-bottom">
                    <div>
                        <p>Made this <span class="inline-block min-w-8 border-b border-slate-700 text-center">{{ $complaint->created_at->format('jS') }}</span> day of <span class="inline-block min-w-32 border-b border-slate-700 text-center">{{ $complaint->created_at->format('F Y') }}</span></p>
                    </div>
                    <div class="paper-signature">
                        <p class="font-semibold">{{ $complaint->resident->name }}</p>
                        <div class="paper-signature-line"></div>
                        <p>(Complainant/s)</p>
                    </div>
                    <div>
                        <p>Received and filed this ______ day of __________________</p>
                    </div>
                    <div class="paper-signature">
                        <p class="font-bold">HON. EDGARDO R. CAMASIN</p>
                        <div class="paper-signature-line"></div>
                        <p>Punong Barangay / Lupon Chairman</p>
                    </div>
                </div>

            </section>
        @endif
        <section class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-widest text-maroon">{{ $complaint->reference }} · {{ $categoryLabel }}</p><h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ $complaint->title }}</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Submitted {{ $complaint->created_at->format('F j, Y \a\t g:i A') }}</p></div>@include('partials.status-badge', ['complaint' => $complaint])</div>
            <div class="mt-7 grid gap-5 sm:grid-cols-2"><div class="rounded-2xl bg-stone-50 p-4 dark:bg-slate-800"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Reported by</p><p class="mt-2 font-semibold">{{ $complaint->resident->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $complaint->resident->phone }}</p></div><div class="rounded-2xl bg-stone-50 p-4 dark:bg-slate-800"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Location</p><p class="mt-2 font-semibold">{{ $complaint->location }}</p></div></div>
            <div class="mt-6"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Description</p><p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-200">{{ $complaint->description }}</p></div>
            @if ($complaint->status === 'rejected' && $complaint->rejection_reason)<div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950/40"><p class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-200">Reason for rejection</p><p class="mt-2 whitespace-pre-line text-sm text-rose-900 dark:text-rose-100">{{ $complaint->rejection_reason }}</p></div>@endif
        </section>

        <section class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h3 class="text-lg font-bold">Private discussion</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visible only to the reporting resident and barangay staff/admin. Other residents cannot see these messages.</p>
            </div>
            <ol class="mt-5 space-y-4">
                @forelse ($complaint->comments as $comment)
                    <li class="rounded-2xl bg-stone-50 p-4 dark:bg-slate-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold">{{ $comment->author?->name ?? 'Former user' }}<span class="ml-2 text-xs font-normal text-slate-500">{{ $comment->author?->id === auth()->id() ? 'You' : ucfirst($comment->author?->role ?? 'account') }}</span></p>
                            <time class="text-xs text-slate-500">{{ $comment->created_at->format('M j, Y · g:i A') }}</time>
                        </div>
                        @if ($comment->body)
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $comment->body }}</p>
                        @endif
                        @if ($canViewEvidence && $comment->evidence->isNotEmpty())
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($comment->evidence as $evidence)
                                    @if (str_starts_with($evidence->mime_type, 'image/'))
                                        <a href="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" target="_blank" rel="noopener" class="overflow-hidden rounded-xl border border-stone-200 dark:border-slate-700">
                                            <img src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" alt="Photo shared in the complaint discussion" class="max-h-64 w-full object-cover">
                                            <span class="block truncate px-3 py-2 text-xs text-slate-500">{{ $evidence->original_name }}</span>
                                        </a>
                                    @else
                                        <div class="rounded-xl border border-stone-200 p-3 dark:border-slate-700">
                                            <video controls preload="metadata" class="w-full rounded-lg" src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}"></video>
                                            <p class="mt-2 truncate text-xs text-slate-500">{{ $evidence->original_name }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="rounded-2xl bg-stone-50 p-4 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">No messages yet. Send a message to discuss this complaint with the barangay.</li>
                @endforelse
            </ol>
            @if ($complaint->status !== 'closed')
                <form method="POST" enctype="multipart/form-data" action="{{ route($role.'.complaints.comments.store', $complaint) }}" class="mt-5 space-y-3 border-t border-stone-100 pt-5 dark:border-slate-800">
                    @csrf
                    <label for="comment-body" class="text-sm font-semibold">Add a message</label>
                    <textarea id="comment-body" name="body" rows="4" maxlength="5000" class="w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700" placeholder="Write a message or attach a photo/video...">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                    @if ($canViewEvidence)
                        <div>
                            <label for="comment-attachments" class="text-sm font-semibold">Attach photos or videos <span class="font-normal text-slate-500">(up to 5 files, 20 MB each)</span></label>
                            <input id="comment-attachments" type="file" name="attachments[]" multiple accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm" class="mt-2 block w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-maroon dark:border-slate-700 dark:file:bg-slate-700 dark:file:text-rose-200">
                            @error('attachments')
                                <p class="mt-1 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                            @foreach ($errors->get('attachments.*') as $messages)
                                @foreach ($messages as $message)
                                    <p class="mt-1 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                    <button class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-bold text-white hover:bg-maroon-dark">Send message</button>
                </form>
            @else
                <p class="mt-5 border-t border-stone-100 pt-4 text-sm text-slate-500 dark:border-slate-800">This ticket was auto-closed after three days without a staff response. The discussion is read-only.</p>
            @endif
        </section>

        @if ($canViewEvidence && $complaint->evidence->whereNull('complaint_comment_id')->isNotEmpty())
            <section class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><h3 class="text-lg font-bold">Supporting evidence</h3><div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($complaint->evidence->whereNull('complaint_comment_id') as $evidence)
                    @if (str_starts_with($evidence->mime_type, 'image/'))
                        <a href="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" target="_blank" rel="noopener" class="overflow-hidden rounded-2xl border border-stone-200 dark:border-slate-700"><img src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" alt="Evidence attached to {{ $complaint->reference }}" class="h-64 w-full object-cover"><p class="truncate px-3 py-2 text-xs text-slate-500">{{ $evidence->original_name }}</p></a>
                    @else
                        <div class="rounded-2xl border border-stone-200 p-3 dark:border-slate-700"><video controls preload="metadata" class="w-full rounded-xl" src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}"></video><p class="mt-2 truncate text-xs text-slate-500">{{ $evidence->original_name }}</p></div>
                    @endif
                @endforeach
            </div></section>
        @endif

        @if ($complaint->meetings->isNotEmpty())
            <section class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold">{{ __('Minutes and settlement records') }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('A record of mediation or hearing outcomes.') }}</p>
                    </div>
                </div>
                <ol class="mt-5 space-y-4">
                    @foreach ($complaint->meetings as $meeting)
                        <li class="rounded-2xl bg-stone-50 p-4 dark:bg-slate-800">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ __(ucfirst($meeting->meeting_type)) }} · {{ $meeting->meeting_date->format('M j, Y') }}</p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-wide {{ $meeting->outcome === 'unsettled' ? 'text-violet-700 dark:text-violet-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ __($meeting->outcome === 'amicable_settlement' ? 'Amicable settlement' : 'Unsettled') }}</p>
                                </div>
                                <a href="{{ route('complaint-meetings.print', [$complaint, $meeting]) }}" class="rounded-lg border border-stone-300 px-3 py-2 text-xs font-bold text-maroon hover:bg-white dark:border-slate-600 dark:text-rose-200 dark:hover:bg-slate-700">{{ __('Print minutes / agreement') }}</a>
                            </div>
                            @if ($meeting->participants)
                                <p class="mt-3 text-xs text-slate-500"><span class="font-semibold">{{ __('Participants') }}:</span> {{ $meeting->participants }}</p>
                            @endif
                            <p class="mt-3 whitespace-pre-line text-sm leading-6">{{ $meeting->minutes }}</p>
                            @if ($meeting->settlement_terms)
                                <div class="mt-3 border-t border-stone-200 pt-3 dark:border-slate-700">
                                    <p class="text-xs font-bold uppercase tracking-wide">{{ __('Settlement terms') }}</p>
                                    <p class="mt-1 whitespace-pre-line text-sm leading-6">{{ $meeting->settlement_terms }}</p>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <section class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><div class="flex items-center justify-between"><div><h3 class="text-lg font-bold">Status timeline</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">A record of every update to this report.</p></div><span class="grid size-10 place-items-center rounded-xl bg-rose-50 font-bold text-maroon dark:bg-slate-800">↗</span></div>
            <ol class="mt-6 space-y-5">
                @foreach ($complaint->activities as $activity)
                    <li class="relative flex gap-4"><span class="mt-1 grid size-9 shrink-0 place-items-center rounded-xl bg-rose-50 text-xs font-bold text-maroon dark:bg-slate-800">{{ $activity->to_status ? '•' : '↗' }}</span><div class="min-w-0 flex-1"><div class="flex flex-wrap justify-between gap-x-4 gap-y-1"><p class="text-sm font-bold">{{ $activity->description ?: ucfirst(str_replace('.', ' ', $activity->action)) }}</p><time class="text-xs text-slate-500">{{ $activity->created_at->format('M j, Y · g:i A') }}</time></div><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $activity->actor?->name ?? 'System' }}@if ($activity->to_status) · {{ ucfirst($activity->to_status === 'approved' ? 'In Progress' : $activity->to_status) }}@endif</p></div></li>
                @endforeach
            </ol>
        </section>
    </div>

    <aside class="space-y-5">
        <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h3 class="font-bold">Assigned staff</h3><p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $complaint->assignedStaff?->name ?? 'Not assigned yet' }}</p></section>
        @if ($role === 'resident' && $complaint->status === 'approved')
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold">Receipt and summons</h3>
                @if ($complaint->receipt_confirmed_at)
                    <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">Staff confirmed your receipt proof.</p>
                    @if ($complaint->summons_ready_at)
                        <a href="{{ route('resident.complaints.summons', $complaint) }}" class="mt-4 block rounded-xl bg-maroon px-4 py-3 text-center text-sm font-bold text-white">View summons</a>
                    @else
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">The staff is preparing your summons.</p>
                    @endif
                @elseif ($complaint->receipt_uploaded_at)
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Your receipt proof was sent. The staff will confirm it before preparing the summons.</p>
                    <a href="{{ route('complaint-receipt.print', $complaint) }}" class="mt-3 inline-block text-sm font-semibold text-maroon hover:underline dark:text-rose-200">{{ __('Print receipt acknowledgment') }}</a>
                @else
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Visit the barangay office to collect your receipt, then upload a photo or PDF here. Staff will confirm it before preparing your summons.</p>
                @endif
            </section>
        @endif
        @if (in_array($role, ['staff', 'admin'], true) && $complaint->status === 'approved')
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold">Receipt and summons</h3>
                @if ($complaint->receipt_proof_path)
                    <a href="{{ route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => $role]) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-maroon hover:underline dark:text-rose-200">Review resident's receipt proof</a>
                    <a href="{{ route('complaint-receipt.print', $complaint) }}" class="mt-2 inline-block text-sm font-semibold text-maroon hover:underline dark:text-rose-200">{{ __('Print receipt acknowledgment') }}</a>
                    @if (! $complaint->receipt_confirmed_at)
                        <form method="POST" action="{{ route($role.'.complaints.receipt.confirm', $complaint) }}" class="mt-4" onsubmit="return confirm('Confirm that the resident has received the receipt?')">
                            @csrf
                            <button class="w-full rounded-xl bg-maroon px-4 py-3 text-sm font-bold text-white">Confirm receipt and continue</button>
                        </form>
                    @else
                        <p class="mt-3 text-sm text-emerald-700 dark:text-emerald-300">Receipt confirmed {{ $complaint->receipt_confirmed_at->format('M j, Y') }}.</p>
                        <a href="{{ route($role.'.complaints.summons', $complaint) }}" class="mt-3 block rounded-xl bg-maroon px-4 py-3 text-center text-sm font-bold text-white">{{ $complaint->summons_ready_at ? 'View or edit summons' : 'Prepare summons' }}</a>
                    @endif
                @else
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Waiting for the resident to collect their receipt and upload proof.</p>
                @endif
            </section>
        @endif
        @if ($canViewEvidence && $complaint->summons_ready_at)
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold">{{ __('Summons service record') }}</h3>
                <ol class="mt-4 space-y-3">
                    @forelse ($complaint->summonsServices as $service)
                        <li class="rounded-xl bg-stone-50 p-3 text-sm dark:bg-slate-800">
                            <p class="font-semibold">{{ __(match ($service->result) { 'served' => 'Served', 'refused' => 'Refused to sign', default => 'Unable to serve' }) }} · {{ $service->served_at->format('M j, Y · g:i A') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Served by') }} {{ $service->server_name }}@if ($service->recipient_name) · {{ $service->recipient_name }}@endif</p>
                            @if ($service->refusal_response)
                                <p class="mt-2 whitespace-pre-line text-xs">{{ $service->refusal_response }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-3">
                                <a href="{{ route('summons-services.proof', $service) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-maroon hover:underline dark:text-rose-200">{{ __('View signed proof') }}</a>
                                <a href="{{ route('summons-services.print', $service) }}" class="text-xs font-semibold text-maroon hover:underline dark:text-rose-200">{{ __('Print service receipt') }}</a>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">{{ __('No service attempt has been recorded.') }}</li>
                    @endforelse
                </ol>
                <form method="POST" action="{{ route($role.'.complaints.summons-services.store', $complaint) }}" enctype="multipart/form-data" class="mt-5 space-y-3 border-t border-stone-100 pt-4 dark:border-slate-800" x-data="{ result: @js(old('result', 'served')), signer: @js(old('signer_type', 'respondent')) }">
                    @csrf
                    <h4 class="text-sm font-bold">{{ __('Record service attempt') }}</h4>
                    <div>
                        <label for="served_at" class="text-xs font-semibold">{{ __('Service date and time') }}</label>
                        <input id="served_at" name="served_at" type="datetime-local" value="{{ old('served_at', now()->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
                        @error('served_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="server_name" class="text-xs font-semibold">{{ __('Server name') }}</label>
                        <input id="server_name" name="server_name" value="{{ old('server_name') }}" required maxlength="255" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
                        @error('server_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="service_result" class="text-xs font-semibold">{{ __('Service result') }}</label>
                        <select id="service_result" name="result" x-model="result" @change="if (result === 'unable_to_serve') signer = 'none'; if (result === 'refused' && signer === 'respondent') signer = 'server'; if (result === 'served' && signer === 'none') signer = 'respondent'" required class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="served">{{ __('Served') }}</option>
                            <option value="refused">{{ __('Refused to sign') }}</option>
                            <option value="unable_to_serve">{{ __('Unable to serve') }}</option>
                        </select>
                        @error('result')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="result !== 'unable_to_serve'">
                        <label for="recipient_name" class="text-xs font-semibold">{{ __('Respondent name') }}</label>
                        <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" :required="result !== 'unable_to_serve'" maxlength="255" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
                        @error('recipient_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="signer_type" class="text-xs font-semibold">{{ __('Signer') }}</label>
                        <select id="signer_type" name="signer_type" x-model="signer" required class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="respondent">{{ __('Respondent') }}</option>
                            <option value="server">{{ __('Server') }}</option>
                            <option value="none">{{ __('No signature') }}</option>
                        </select>
                        @error('signer_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="signer !== 'none'">
                        <label for="signer_name" class="text-xs font-semibold">{{ __('Signer name') }}</label>
                        <input id="signer_name" name="signer_name" value="{{ old('signer_name') }}" :required="signer !== 'none'" maxlength="255" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
                        @error('signer_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="result === 'refused'">
                        <label for="refusal_response" class="text-xs font-semibold">{{ __('Refusal response') }}</label>
                        <textarea id="refusal_response" name="refusal_response" rows="3" :required="result === 'refused'" maxlength="2000" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700"></textarea>
                        @error('refusal_response')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="service_proof" class="text-xs font-semibold">{{ __('Upload signed proof') }}</label>
                        <input id="service_proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required class="mt-1 block w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2 text-xs dark:border-slate-700">
                        <p class="mt-1 text-xs text-slate-500">{{ __('JPG, PNG, or PDF, up to 5 MB.') }}</p>
                        @error('proof')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">{{ __('Save service record') }}</button>
                </form>
            </section>
        @endif
        @if ($canViewEvidence && in_array($complaint->status, ['approved', 'unsettled'], true))
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" x-data="{ outcome: '{{ old('outcome', 'unsettled') }}' }">
                <h3 class="font-bold">{{ __('Record meeting minutes') }}</h3>
                <form method="POST" action="{{ route($role.'.complaints.meetings.store', $complaint) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="meeting_type" class="text-xs font-semibold">{{ __('Meeting type') }}</label>
                        <select id="meeting_type" name="meeting_type" required class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="mediation" @selected(old('meeting_type') === 'mediation')>{{ __('Mediation') }}</option>
                            <option value="hearing" @selected(old('meeting_type') === 'hearing')>{{ __('Hearing') }}</option>
                        </select>
                        @error('meeting_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="meeting_date" class="text-xs font-semibold">{{ __('Meeting date') }}</label>
                        <input id="meeting_date" name="meeting_date" type="date" value="{{ old('meeting_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
                        @error('meeting_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="participants" class="text-xs font-semibold">{{ __('Participants') }}</label>
                        <textarea id="participants" name="participants" rows="2" maxlength="2000" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">{{ old('participants') }}</textarea>
                    </div>
                    <div>
                        <label for="minutes" class="text-xs font-semibold">{{ __('Minutes') }}</label>
                        <textarea id="minutes" name="minutes" rows="5" minlength="10" maxlength="10000" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">{{ old('minutes') }}</textarea>
                        @error('minutes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="outcome" class="text-xs font-semibold">{{ __('Outcome') }}</label>
                        <select id="outcome" name="outcome" x-model="outcome" required class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="unsettled">{{ __('Unsettled') }}</option>
                            <option value="amicable_settlement">{{ __('Amicable settlement') }}</option>
                        </select>
                        @error('outcome')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="outcome === 'amicable_settlement'">
                        <label for="settlement_terms" class="text-xs font-semibold">{{ __('Settlement terms') }}</label>
                        <textarea id="settlement_terms" name="settlement_terms" rows="4" minlength="5" maxlength="5000" :required="outcome === 'amicable_settlement'" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">{{ old('settlement_terms') }}</textarea>
                        @error('settlement_terms')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">{{ __('Save minutes') }}</button>
                </form>
            </section>
        @endif
        @if ($role === 'resident' && $complaint->status === 'closed')
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold">Ticket closed</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">This complaint was automatically closed because no staff response was recorded within three days.</p>
            </section>
        @endif
        @if (in_array($role, ['staff', 'admin'], true))
            <section class="rounded-3xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold">Update complaint</h3>
                @if ($complaint->status === 'pending')
                    <form method="POST" action="{{ route($role.'.complaints.approve', $complaint) }}" class="mt-4 space-y-3" onsubmit="return confirm('Do you want to approve it?')">@csrf
                        <label for="assigned_staff_id" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Assign to staff <span class="font-normal">(optional)</span></label>
                        <select id="assigned_staff_id" name="assigned_staff_id" class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">Leave unassigned</option>@foreach ($staffMembers as $staffMember)<option value="{{ $staffMember->id }}" @selected($complaint->assigned_staff_id === $staffMember->id)>{{ $staffMember->name }}</option>@endforeach</select>
                        <button class="w-full rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Approve · In progress</button>
                    </form>
                    <form method="POST" action="{{ route($role.'.complaints.reject', $complaint) }}" class="mt-4 space-y-3 border-t border-stone-100 pt-4 dark:border-slate-800" onsubmit="return confirm('Do you want to reject this complaint?')">@csrf
                        <label for="reason" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Reason for rejection</label><textarea id="reason" name="reason" rows="3" required class="w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700" placeholder="Explain why this complaint cannot proceed"></textarea>
                        <button class="w-full rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-200">Reject complaint</button>
                    </form>
                @elseif ($complaint->status === 'approved')
                    <p class="mt-3 text-sm text-slate-500">{{ ! $complaint->receipt_proof_path ? 'The resident was notified to collect a receipt. Waiting for proof upload.' : (! $complaint->receipt_confirmed_at ? 'Review the uploaded receipt proof and confirm it to continue to the summons.' : (! $complaint->summons_ready_at ? 'Receipt proof is confirmed. Prepare the summons next.' : 'The summons is ready for the resident to view.')) }}</p>
                    @if ($role === 'staff')
                        <form method="POST" action="{{ route('staff.complaints.resolve', $complaint) }}" class="mt-4 border-t border-stone-100 pt-4 dark:border-slate-800" onsubmit="return confirm('Mark this complaint as solved? The resident will be notified.')">
                            @csrf
                            <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800">Mark as solved</button>
                        </form>
                    @endif
                @elseif ($complaint->status === 'closed')
                    <p class="mt-3 text-sm text-slate-500">This ticket was automatically closed because no staff response was recorded within three days.</p>
                @else
                    <p class="mt-3 text-sm text-slate-500">This complaint has already been handled.</p>
                @endif
            </section>
        @endif
    </aside>
</div>
@if ($role === 'resident' && $complaint->status === 'approved' && ! $complaint->receipt_uploaded_at)
    <div x-data="{ open: true }">
        <div x-cloak x-show="open" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/70 p-4" role="presentation">
            <section role="dialog" aria-modal="true" aria-labelledby="receipt-proof-title" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-maroon dark:text-rose-200">Next step</p>
                <h2 id="receipt-proof-title" class="mt-2 text-xl font-black">Upload your receipt proof</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Your complaint was approved. Please collect the receipt from the barangay office, then attach a clear photo or PDF here. Staff must confirm it before the summons can be prepared.</p>
                <form method="POST" action="{{ route('resident.complaints.receipt.store', $complaint) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="receipt_proof" class="text-sm font-semibold">Receipt photo or PDF</label>
                        <input id="receipt_proof" name="receipt_proof" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required class="mt-2 block w-full rounded-xl border border-stone-300 bg-transparent px-3 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:font-semibold file:text-maroon dark:border-slate-700 dark:file:bg-slate-800 dark:file:text-rose-200">
                        <p class="mt-1 text-xs text-slate-500">JPG, PNG, or PDF, up to 5 MB.</p>
                        @error('receipt_proof')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-xl bg-maroon px-4 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Send proof to staff</button>
                </form>
                <button type="button" @click="open = false" class="mt-3 w-full rounded-xl border border-stone-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-stone-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Upload later</button>
            </section>
        </div>
        <button type="button" x-show="!open" @click="open = true" class="fixed bottom-5 right-5 z-40 rounded-full bg-maroon px-5 py-3 text-sm font-bold text-white shadow-lg">Upload receipt proof</button>
    </div>
@endif
@endsection
