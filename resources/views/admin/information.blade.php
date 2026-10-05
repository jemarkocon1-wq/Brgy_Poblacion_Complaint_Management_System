@extends('layouts.app')

@section('title', 'Barangay information')
@section('heading', 'Barangay information')

@section('content')
<div class="max-w-3xl"><div class="mb-6"><p class="text-sm text-slate-500 dark:text-slate-400">Keep public contact details up to date.</p><h2 class="mt-1 text-2xl font-black">Barangay profile</h2></div>
    <form method="POST" action="{{ route('admin.information.update') }}" class="rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">@csrf @method('PATCH')
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach (['name' => 'Barangay name', 'municipality' => 'Municipality', 'province' => 'Province', 'address' => 'Office address', 'phone' => 'Contact phone', 'email' => 'Contact email'] as $field => $label)
                <div class="{{ $field === 'address' ? 'sm:col-span-2' : '' }}"><label for="{{ $field }}" class="text-sm font-semibold">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $information->$field) }}" @required($field === 'name') class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error($field)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            @endforeach
            <div class="sm:col-span-2"><label for="office_hours" class="text-sm font-semibold">Office hours</label><textarea id="office_hours" name="office_hours" rows="3" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">{{ old('office_hours', $information->office_hours) }}</textarea></div>
        </div>
        <button class="mt-6 rounded-xl bg-maroon px-5 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Save barangay information</button>
    </form>
</div>
@endsection
