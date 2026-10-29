@extends('layouts.app')

@php($role = $role ?? auth()->user()->role)

@section('title', $role === 'resident' ? 'Submit a complaint' : 'Encode a complaint')
@section('heading', $role === 'resident' ? 'Submit a complaint' : 'Encode a complaint')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route($role.'.complaints.index') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← {{ $role === 'resident' ? 'My complaints' : 'All complaints' }}</a>
    <div class="mt-4 rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-maroon">{{ $role === 'resident' ? 'Tell us what happened' : 'Record a complaint for a resident' }}</p><h2 class="mt-2 text-2xl font-black">{{ $role === 'resident' ? "We'll work on it together." : 'Encode a complaint' }}</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $role === 'resident' ? 'Give us enough detail to understand and locate your concern.' : 'Select the resident and enter the case details. Staff may attach private evidence.' }}</p>
        <form id="complaint-form" method="POST" action="{{ route($role.'.complaints.store') }}" enctype="multipart/form-data" class="mt-7 space-y-5" x-data="{ category: @js(old('category', '')) }">@csrf
            @if ($role !== 'resident')
                <div>
                    <label for="resident_id" class="text-sm font-semibold">Resident</label>
                    <select id="resident_id" name="resident_id" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Choose a resident</option>
                        @foreach ($residents as $resident)
                            <option value="{{ $resident->id }}" @selected(old('resident_id') == $resident->id)>{{ $resident->name }} · {{ $resident->email }}</option>
                        @endforeach
                    </select>
                    @error('resident_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            @endif
            <div><label for="category" class="text-sm font-semibold">{{ __('Category') }}</label><select id="category" name="category" x-model="category" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">{{ __('Choose a category') }}</option><option value="civil">{{ __('Civil') }}</option><option value="criminal_case">{{ __('Criminal case') }}</option><option value="other">{{ __('Others') }}</option></select>@error('category')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="title" class="text-sm font-semibold">Short title</label><input id="title" name="title" value="{{ old('title') }}" maxlength="160" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="respondents" class="text-sm font-semibold">Respondent/s</label><textarea id="respondents" name="respondents" rows="2" maxlength="2000" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" placeholder="Name the person or persons this complaint is against">{{ old('respondents') }}</textarea>@error('respondents')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="case_subject" class="text-sm font-semibold" x-text="category === 'criminal_case' ? 'Alleged offense (For)' : 'Nature of dispute (For)'">Nature of dispute (For)</label><input id="case_subject" name="case_subject" value="{{ old('case_subject') }}" maxlength="160" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" :placeholder="category === 'criminal_case' ? 'e.g. alleged theft or physical injury' : 'e.g. land dispute or unpaid obligation'">@error('case_subject')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label for="location" class="text-sm font-semibold" x-text="category === 'criminal_case' ? 'Incident location in the barangay' : 'Location of the dispute'">Location of the dispute</label><input id="location" name="location" value="{{ old('location') }}" required placeholder="Street, landmark, or purok" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('location')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div x-cloak x-show="category === 'criminal_case'"><label for="incident_date" class="text-sm font-semibold">Date of incident</label><input id="incident_date" name="incident_date" type="date" value="{{ old('incident_date') }}" :required="category === 'criminal_case'" max="{{ now()->toDateString() }}" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('incident_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            </div>
            <div><label for="description" class="text-sm font-semibold" x-text="category === 'criminal_case' ? 'Describe the incident' : 'Describe what happened'">Describe what happened</label><textarea id="description" name="description" rows="6" required minlength="20" maxlength="5000" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" placeholder="Describe what happened and include relevant details">{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div x-cloak x-show="category === 'civil'"><label for="relief_sought" class="text-sm font-semibold">Relief or solution requested <span class="font-normal text-slate-500">(optional)</span></label><textarea id="relief_sought" name="relief_sought" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" placeholder="What resolution are you asking the barangay to help with?">{{ old('relief_sought') }}</textarea>@error('relief_sought')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div x-cloak x-show="category === 'criminal_case'"><label for="witnesses" class="text-sm font-semibold">Witnesses or supporting evidence <span class="font-normal text-slate-500">(optional)</span></label><textarea id="witnesses" name="witnesses" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" placeholder="List witness names or describe other supporting evidence">{{ old('witnesses') }}</textarea>@error('witnesses')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            @if ($role !== 'resident')
                <div><label for="attachments" class="text-sm font-semibold">Photos or videos <span class="font-normal text-slate-500">(optional · up to 5 files)</span></label><input id="attachments" name="attachments[]" type="file" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" class="mt-1.5 block w-full rounded-xl border border-stone-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-rose-50 file:px-4 file:py-3 file:font-semibold file:text-maroon dark:border-slate-700 dark:file:bg-slate-800">@error('attachments')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @error('attachments.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror<p class="mt-2 text-xs text-slate-500">JPG, PNG or WebP images up to 5 MB; MP4 or WebM videos up to 50 MB.</p></div>
            @endif
            @if ($role !== 'resident')
                <div id="attachment-preview" class="hidden grid gap-3 sm:grid-cols-2"></div>
            @endif
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-stone-100 pt-5 sm:flex-row dark:border-slate-800"><a href="{{ route($role.'.complaints.index') }}" class="rounded-xl border border-stone-200 px-5 py-3 text-center text-sm font-semibold dark:border-slate-700">Cancel</a><button class="rounded-xl bg-maroon px-6 py-3 text-sm font-bold text-white hover:bg-maroon-dark">{{ $role === 'resident' ? 'Submit complaint' : 'Save complaint' }}</button></div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('complaint-form');
        const input = document.getElementById('attachments');
        const preview = document.getElementById('attachment-preview');

        if (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm('Are you sure, you want to submit this complaint?') || !window.confirm('Surreeeee?')) {
                    event.preventDefault();
                }
            });
        }

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function (event) {
            const files = Array.from(event.target.files ?? []);
            preview.innerHTML = '';
            preview.classList.add('hidden');

            if (files.length === 0) {
                return;
            }

            files.forEach((file) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'overflow-hidden rounded-2xl border border-stone-200 bg-stone-50 p-2 dark:border-slate-700 dark:bg-slate-800/60';

                const meta = document.createElement('p');
                meta.textContent = file.name;
                meta.className = 'mb-2 truncate text-xs font-semibold text-slate-600 dark:text-slate-300';

                const media = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
                media.src = URL.createObjectURL(file);
                media.className = 'h-40 w-full rounded-xl object-cover';
                if (file.type.startsWith('video/')) {
                    media.controls = true;
                    media.preload = 'metadata';
                }

                wrapper.appendChild(meta);
                wrapper.appendChild(media);
                preview.appendChild(wrapper);
            });

            preview.classList.remove('hidden');
        });
    });
</script>
@endsection
