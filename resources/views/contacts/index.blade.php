@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-end">
        <a href="{{ route('contacts.create', $searchQueryParams ?? []) }}" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-semibold text-white">
            New Contact
        </a>
    </div>

    @if ($contacts->count() === 0)
        <div class="rounded-lg border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600">
            No contacts found.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @foreach ($contacts as $contact)
                        <tr class="hover:bg-zinc-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-zinc-900">
                                    {{ trim(($contact->firstName ?? '') . ' ' . ($contact->lastName ?? '')) ?: '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-700">
                                {{ $contact->email }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('contacts.show', ['contact' => $contact->uuid->toString(), ...($searchQueryParams ?? [])]) }}" class="text-zinc-700 hover:text-zinc-900">
                                        View
                                    </a>
                                    <a href="{{ route('contacts.edit', ['contact' => $contact->uuid->toString(), ...($searchQueryParams ?? [])]) }}" class="text-zinc-700 hover:text-zinc-900">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $contacts->links() }}
        </div>
    @endif
@endsection
