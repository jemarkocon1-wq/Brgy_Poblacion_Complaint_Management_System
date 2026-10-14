@extends('layouts.app')

@section('title', $complaint->reference)
@section('heading', 'Complaint details')

@section('content')
@php
    $role = auth()->user()->role;
    $baseRoute = $role === 'admin' ? 'admin.complaints.index' : ($role === 'staff' ? 'staff.complaints.index' : 'resident.complaints.index');
@endphp
<div class="mb-5"><a href="{{ route($baseRoute) }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← Back to complaints</a></div>
<div class="grid gap-6 xl:grid-cols-[1fr_330px]">
    <div class="space-y-6">
        <section class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-widest text-maroon">{{ $complaint->reference }} · {{ str_replace('_', ' ', $complaint->category) }}</p><h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ $complaint->title }}</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Submitted {{ $complaint->created_at->format('F j, Y \a\t g:i A') }}</p></div>@include('partials.status-badge', ['complaint' => $complaint])</div>
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
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $comment->body }}</p>
                    </li>
                @empty
                    <li class="rounded-2xl bg-stone-50 p-4 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">No messages yet. Send a message to discuss this complaint with the barangay.</li>
                @endforelse
            </ol>
            <form method="POST" action="{{ route($role.'.complaints.comments.store', $complaint) }}" class="mt-5 space-y-3 border-t border-stone-100 pt-5 dark:border-slate-800">
                @csrf
                <label for="comment-body" class="text-sm font-semibold">Add a message</label>
                <textarea id="comment-body" name="body" rows="4" maxlength="5000" required class="w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm outline-none focus:border-maroon focus:ring-2 focus:ring-maroon/15 dark:border-slate-700" placeholder="Write a message about this complaint...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p>
                @enderror
                <button class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-bold text-white hover:bg-maroon-dark">Send message</button>
            </form>
        </section>

        @if ($complaint->evidence->isNotEmpty())
            <section class="rounded-3xl border border-stone-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"><h3 class="text-lg font-bold">Supporting evidence</h3><div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach ($complaint->evidence as $evidence)
                    @if (str_starts_with($evidence->mime_type, 'image/'))
                        <a href="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" target="_blank" rel="noopener" class="overflow-hidden rounded-2xl border border-stone-200 dark:border-slate-700"><img src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}" alt="Evidence attached to {{ $complaint->reference }}" class="h-64 w-full object-cover"><p class="truncate px-3 py-2 text-xs text-slate-500">{{ $evidence->original_name }}</p></a>
                    @else
                        <div class="rounded-2xl border border-stone-200 p-3 dark:border-slate-700"><video controls preload="metadata" class="w-full rounded-xl" src="{{ route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => $role]) }}"></video><p class="mt-2 truncate text-xs text-slate-500">{{ $evidence->original_name }}</p></div>
                    @endif
                @endforeach
            </div></section>
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
                    <form method="POST" action="{{ route($role.'.complaints.resolve', $complaint) }}" class="mt-4" onsubmit="return confirm('Are you sure?')">@csrf<button class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800">Mark as resolved</button></form>
                @else
                    <p class="mt-3 text-sm text-slate-500">This complaint has already been handled.</p>
                @endif
            </section>
        @endif
    </aside>
</div>
@endsection
