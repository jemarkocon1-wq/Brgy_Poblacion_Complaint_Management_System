@extends('layouts.app')

@section('title', 'Submit a complaint')
@section('heading', 'Submit a complaint')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('resident.complaints.index') }}" class="text-sm font-semibold text-slate-500 hover:text-maroon">← My complaints</a>
    <div class="mt-4 rounded-3xl border border-stone-200 bg-white p-6 sm:p-8 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-maroon">Tell us what's happening</p><h2 class="mt-2 text-2xl font-black">We'll work on it together.</h2><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Give us enough detail to understand and locate your concern. You may attach up to five photos or videos.</p>
        <form method="POST" action="{{ route('resident.complaints.store') }}" enctype="multipart/form-data" class="mt-7 space-y-5">@csrf
            <div><label for="title" class="text-sm font-semibold">Short title</label><input id="title" name="title" value="{{ old('title') }}" maxlength="160" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div class="grid gap-5 sm:grid-cols-2"><div><label for="category" class="text-sm font-semibold">Category</label><select id="category" name="category" required class="mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">Choose a category</option>@foreach (['noise' => 'Noise concern', 'sanitation' => 'Sanitation and waste', 'infrastructure' => 'Roads and infrastructure', 'public_safety' => 'Public safety', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>@endforeach</select>@error('category')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="location" class="text-sm font-semibold">Location in the barangay</label><input id="location" name="location" value="{{ old('location') }}" required placeholder="Street, landmark, or purok" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700">@error('location')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            </div>
            <div><label for="description" class="text-sm font-semibold">Describe your concern</label><textarea id="description" name="description" rows="6" required minlength="20" maxlength="5000" class="mt-1.5 w-full rounded-xl border border-stone-300 bg-transparent px-4 py-3 text-sm dark:border-slate-700" placeholder="What happened? When did you notice it?">{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="attachments" class="text-sm font-semibold">Photos or videos <span class="font-normal text-slate-500">(optional · up to 5 files)</span></label><input id="attachments" name="attachments[]" type="file" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" class="mt-1.5 block w-full rounded-xl border border-stone-300 bg-transparent text-sm file:mr-4 file:border-0 file:bg-rose-50 file:px-4 file:py-3 file:font-semibold file:text-maroon dark:border-slate-700 dark:file:bg-slate-800">@error('attachments')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @error('attachments.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror<p class="mt-2 text-xs text-slate-500">JPG, PNG or WebP images up to 5 MB; MP4 or WebM videos up to 50 MB.</p></div>
            <div id="attachment-preview" class="hidden grid gap-3 sm:grid-cols-2"></div>
            <div class="flex flex-col-reverse justify-end gap-3 border-t border-stone-100 pt-5 sm:flex-row dark:border-slate-800"><a href="{{ route('resident.complaints.index') }}" class="rounded-xl border border-stone-200 px-5 py-3 text-center text-sm font-semibold dark:border-slate-700">Cancel</a><button class="rounded-xl bg-maroon px-6 py-3 text-sm font-bold text-white hover:bg-maroon-dark">Submit complaint</button></div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('attachments');
        const preview = document.getElementById('attachment-preview');

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
