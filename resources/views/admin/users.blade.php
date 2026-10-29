@extends('layouts.app')

@section('title', 'User management')
@section('heading', 'Users')

@section('content')
<div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">Create and maintain admin, resident, and barangay staff accounts.</p><h2 class="mt-1 text-2xl font-black">People and access</h2></div>
<form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-[1fr_180px_auto_auto] dark:border-slate-800 dark:bg-slate-900">
    <input name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone" class="w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">
    <select name="role" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
        <option value="">All roles</option>
        <option value="admin" @selected(request('role') === 'admin')>Admins</option>
        <option value="resident" @selected(request('role') === 'resident')>Residents</option>
        <option value="staff" @selected(request('role') === 'staff')>Staff</option>
    </select>
    <button class="rounded-xl bg-maroon px-4 py-2.5 text-sm font-bold text-white">Apply filters</button>
    @if (request()->hasAny(['role', 'search']))
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-stone-200 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700">Clear</a>
    @endif
</form>
<details class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><summary class="cursor-pointer list-none font-bold text-maroon">＋ Add an account</summary>
    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">@csrf
        <div><label class="text-xs font-semibold" for="new-name">Full name</label><input id="new-name" name="name" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">@error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
        <div><label class="text-xs font-semibold" for="new-email">Email</label><input id="new-email" name="email" type="email" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">@error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
        <div><label class="text-xs font-semibold" for="new-phone">Phone</label><input id="new-phone" name="phone" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700"></div>
        <div><label class="text-xs font-semibold" for="new-role">Account type</label><select id="new-role" name="role" required class="mt-1 w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="staff" @selected(old('role') === 'staff')>Barangay staff</option><option value="resident" @selected(old('role') === 'resident')>Resident</option><option value="admin" @selected(old('role') === 'admin')>Admin</option></select></div>
        <div><label class="text-xs font-semibold" for="new-password">Temporary password</label><input id="new-password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700">@error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
        <div><label class="text-xs font-semibold" for="new-password-confirmation">Confirm password</label><input id="new-password-confirmation" name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-stone-300 bg-transparent px-3 py-2.5 text-sm dark:border-slate-700"></div>
        <div class="sm:col-span-2"><button class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-bold text-white">Create account</button></div>
    </form>
</details>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white dark:border-slate-800 dark:bg-slate-900"><div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm"><thead class="bg-stone-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800"><tr><th class="px-5 py-4">Account</th><th class="px-5 py-4">Role</th><th class="px-5 py-4">Reports</th><th class="px-5 py-4">Access</th><th class="px-5 py-4">Manage</th></tr></thead><tbody class="divide-y divide-stone-100 dark:divide-slate-800">
@forelse ($users as $managedUser)
<tr><td class="px-5 py-4"><p class="font-bold">{{ $managedUser->name }}</p><p class="text-xs text-slate-500">{{ $managedUser->email }} · {{ $managedUser->phone ?? 'no phone' }}</p></td><td class="px-5 py-4 capitalize">{{ $managedUser->role }}</td><td class="px-5 py-4">{{ $managedUser->role === 'resident' ? $managedUser->complaints_count : $managedUser->assigned_complaints_count.' assigned' }}</td><td class="px-5 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $managedUser->is_active, 'bg-rose-100 text-rose-800' => ! $managedUser->is_active])>{{ $managedUser->is_active ? 'Active' : 'Disabled' }}</span></td>
<td class="px-5 py-4"><div class="flex items-center gap-3">@if ($managedUser->role === 'admin')<span class="text-xs text-slate-500">Protected</span>@else<details><summary class="cursor-pointer font-bold text-maroon">Edit</summary><form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="absolute z-10 mt-2 grid w-80 gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900">@csrf @method('PATCH')
<label class="text-xs font-semibold">Name<input name="name" value="{{ $managedUser->name }}" required class="mt-1 w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 text-sm dark:border-slate-700"></label>
<label class="text-xs font-semibold">Email<input name="email" type="email" value="{{ $managedUser->email }}" required class="mt-1 w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 text-sm dark:border-slate-700"></label>
<label class="text-xs font-semibold">Phone<input name="phone" value="{{ $managedUser->phone }}" class="mt-1 w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 text-sm dark:border-slate-700"></label>
<label class="text-xs font-semibold">Role<select name="role" class="mt-1 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="resident" @selected($managedUser->role === 'resident')>Resident</option><option value="staff" @selected($managedUser->role === 'staff')>Staff</option></select></label>
<label class="text-xs font-semibold">New password <span class="font-normal">(leave blank to keep current)</span><input name="password" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 text-sm dark:border-slate-700"></label>
<label class="text-xs font-semibold">Confirm password<input name="password_confirmation" type="password" class="mt-1 w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 text-sm dark:border-slate-700"></label>
<button class="rounded-lg bg-maroon px-3 py-2 text-xs font-bold text-white">Save changes</button></form></details>
<form method="POST" action="{{ route('admin.users.access', $managedUser) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $managedUser->is_active ? '0' : '1' }}"><button class="text-xs font-semibold {{ $managedUser->is_active ? 'text-rose-700' : 'text-emerald-700' }}">{{ $managedUser->is_active ? 'Disable' : 'Enable' }}</button></form>@endif</div></td></tr>
@empty<tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No accounts to manage yet.</td></tr>@endforelse
</tbody></table></div><div class="border-t border-stone-100 px-5 py-4 dark:border-slate-800">{{ $users->links() }}</div></div>
@endsection
