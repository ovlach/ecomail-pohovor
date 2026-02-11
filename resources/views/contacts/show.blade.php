@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">
                {{ trim(($contact->firstName ?? '') . ' ' . ($contact->lastName ?? '')) ?: 'Contact' }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600">{{ $contact->email }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('contacts.edit', ['contact' => $contact->uuid->toString(), ...($searchQueryParams ?? [])]) }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700">
                Edit
            </a>
            <form method="post" action="{{ route('contacts.destroy', ['contact' => $contact->uuid->toString(), ...($searchQueryParams ?? [])]) }}" onsubmit="return confirm('Delete this contact?')">
                @csrf
                @method('delete')
                <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6">
        <dl class="grid gap-4 text-sm">
            <div>
                <dt class="font-medium text-zinc-700">First name</dt>
                <dd class="mt-1 text-zinc-900">{{ $contact->firstName ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-zinc-700">Last name</dt>
                <dd class="mt-1 text-zinc-900">{{ $contact->lastName ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-zinc-700">Email</dt>
                <dd class="mt-1 text-zinc-900">{{ $contact->email }}</dd>
            </div>
        </dl>
    </div>
@endsection
