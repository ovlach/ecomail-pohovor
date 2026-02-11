@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">New Contact</h1>
        <p class="mt-1 text-sm text-zinc-600">Add a new contact to your list.</p>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6">
        <form method="post" action="{{ route('contacts.store', $searchQueryParams ?? []) }}">
            @csrf
            @include('contacts.partials.form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
