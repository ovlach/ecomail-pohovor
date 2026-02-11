@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Contact</h1>
        <p class="mt-1 text-sm text-zinc-600">Update contact details.</p>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6">
        <form method="post" action="{{ route('contacts.update', ['contact' => $contact->uuid->toString(), ...($searchQueryParams ?? [])]) }}">
            @csrf
            @method('put')
            @include('contacts.partials.form', ['contact' => $contact, 'submitLabel' => 'Save changes'])
        </form>
    </div>
@endsection
