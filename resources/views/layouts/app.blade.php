<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-50 text-zinc-900">
        <div class="min-h-screen">
            <div class="flex min-h-screen">
                <aside class="sticky top-0 hidden h-screen w-64 shrink-0 border-r border-zinc-200 bg-white px-4 py-6 lg:block">
                    <div class="mb-6 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        Ecomail
                    </div>
                    <nav class="space-y-1 text-sm">
                        <a
                            href="{{ route('contacts.index') }}"
                            class="{{ request()->routeIs('contacts.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-700 hover:bg-zinc-100' }} flex items-center rounded-md px-3 py-2 font-medium"
                        >
                            Contacts
                        </a>
                        <a
                            href="{{ route('import.index') }}"
                            class="{{ request()->routeIs('import.*') ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-700 hover:bg-zinc-100' }} flex items-center rounded-md px-3 py-2 font-medium"
                        >
                            Import
                        </a>
                    </nav>
                </aside>

                <div class="flex-1">
                    <header class="sticky top-0 z-10 border-b border-zinc-200 bg-white/80 px-4 backdrop-blur">
                        <div class="mx-auto flex h-16 max-w-5xl items-center gap-4">
                            <div class="lg:hidden">
                                <span class="text-sm font-semibold text-zinc-900">Menu</span>
                            </div>
                            @if (request()->routeIs('contacts.*'))
                                <form method="get" action="{{ route('contacts.search') }}" class="flex-1">
                                    @csrf
                                    @method('get')
                                    <label for="q" class="sr-only">Search contacts</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                            🔎
                                        </span>
                                        <input
                                            id="q"
                                            name="q"
                                            type="text"
                                            placeholder="Search contacts"
                                            value="{{ $searchQuery ?? '' }}"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-9 py-2 text-sm focus:border-zinc-900 focus:outline-none"
                                        />
                                    </div>
                                </form>
                            @elseif (request()->routeIs('import.*'))
                                <h1 class="flex-1 text-center text-sm font-semibold text-zinc-900">
                                    Import Contacts
                                </h1>
                            @endif
                        </div>
                    </header>

                    <main class="mx-auto max-w-5xl px-4 py-8">

                        @if (session('status'))
                            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                {{ session('status') }}
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
