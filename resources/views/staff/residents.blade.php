@extends('layouts.app')

@section('title', 'Residents')
@section('heading', 'Residents')

@section('content')
<div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">Directory of resident accounts and their submitted reports.</p><h2 class="mt-1 text-2xl font-black">Resident directory</h2></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto"><table class="w-full min-w-[620px] text-left text-sm"><thead class="bg-stone-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800"><tr><th class="px-5 py-4">Resident</th><th class="px-5 py-4">Phone</th><th class="px-5 py-4">Reports</th><th class="px-5 py-4">Joined</th></tr></thead><tbody class="divide-y divide-stone-100 dark:divide-slate-800">@forelse ($residents as $resident)<tr><td class="px-5 py-4"><p class="font-bold">{{ $resident->name }}</p><p class="text-xs text-slate-500">{{ $resident->email }}</p></td><td class="px-5 py-4">{{ $resident->phone ?? '—' }}</td><td class="px-5 py-4">{{ $resident->complaints_count }}</td><td class="px-5 py-4 text-slate-500">{{ $resident->created_at->format('M j, Y') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-12 text-center text-slate-500">No resident accounts yet.</td></tr>@endforelse</tbody></table></div><div class="border-t border-stone-100 px-5 py-4 dark:border-slate-800">{{ $residents->links() }}</div></div>
@endsection
