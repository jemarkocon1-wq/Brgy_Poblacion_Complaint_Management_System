@php
    $status = $complaint->status;
    $classes = match ($status) {
        'approved' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
        'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
        'resolved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        'unsettled' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
        'closed' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    };
@endphp
<span data-complaint-status="{{ $complaint->id }}" data-status="{{ $status }}" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">{{ $complaint->statusLabel() }}</span>
