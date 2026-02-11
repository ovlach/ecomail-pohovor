@php
    $contact = $contact ?? null;
@endphp

<div class="grid gap-4">
    <div>
        <label for="first_name" class="text-sm font-medium text-zinc-700">First name</label>
        <input
            id="first_name"
            name="first_name"
            type="text"
            value="{{ old('first_name', $contact?->firstName) }}"
            class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-900 focus:outline-none"
        />
        @error('first_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="last_name" class="text-sm font-medium text-zinc-700">Last name</label>
        <input
            id="last_name"
            name="last_name"
            type="text"
            value="{{ old('last_name', $contact?->lastName) }}"
            class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-900 focus:outline-none"
        />
        @error('last_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $contact?->email) }}"
            class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-900 focus:outline-none"
            required
        />
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-semibold text-white">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('contacts.index') }}" class="text-sm text-zinc-700 hover:text-zinc-900">
        Cancel
    </a>
</div>
