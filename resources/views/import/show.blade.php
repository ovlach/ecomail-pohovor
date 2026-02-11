@extends('layouts.app')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusEl = document.getElementById('upload-status');
    if (!statusEl) {
        return;
    }

    const uuid = statusEl.dataset.importUuid;
    if (!uuid) {
        return;
    }

    statusEl.style.display = 'block';

    const formatDuration = (startIso, endIso) => {
        if (!startIso || !endIso) {
            return 'N/A';
        }

        const start = new Date(startIso);
        const end = new Date(endIso);
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            return 'N/A';
        }

        const totalSeconds = Math.max(0, Math.round((end - start) / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
    };

    const render = (data) => {
        const state = data.state || 'UNKNOWN';
        if (state !== 'DONE') {
            statusEl.textContent = `Processing (${state})`;
            return;
        }

        const duration = formatDuration(data.started_at, data.finished_at);
        statusEl.innerHTML = `
            <div class="rounded-md border border-zinc-200 bg-zinc-50 p-4 text-sm">
                <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">
                    Import done
                </div>
                <div class="mt-3">
                    <div class="grid w-full grid-cols-5 gap-2 text-right">
                        <div class="rounded-md border border-zinc-200 bg-white px-3 py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Total</div>
                            <div class="text-sm font-semibold text-zinc-900">${data.total_processed ?? 0}</div>
                        </div>
                        <div class="rounded-md border border-zinc-200 bg-white px-3 py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Imported</div>
                            <div class="text-sm font-semibold text-zinc-900">${data.total_processed - data.errors - data.duplicates}</div>
                        </div>
                        <div class="rounded-md border border-zinc-200 bg-white px-3 py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Failed</div>
                            <div class="text-sm font-semibold text-zinc-900">${data.errors ?? 0}</div>
                        </div>
                        <div class="rounded-md border border-zinc-200 bg-white px-3 py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Duplicates</div>
                            <div class="text-sm font-semibold text-zinc-900">${data.duplicates ?? 0}</div>
                        </div>
                        <div class="rounded-md border border-zinc-200 bg-white px-3 py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Duration</div>
                            <div class="text-sm font-semibold text-zinc-900">${duration}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    const fetchStatus = async () => {
        const response = await fetch(`/api/imports/${uuid}`, {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to load import status');
        }

        return response.json();
    };

    const poll = async () => {
        try {
            const data = await fetchStatus();
            render(data);
            if (data.state === 'DONE' || data.state === 'FAILED') {
                document.getElementById('import-form').style.display = 'block';
                clearInterval(timer);
            }
        } catch (error) {
            statusEl.textContent = 'Failed to load import status.';
            document.getElementById('import-form').style.display = 'block';
            clearInterval(timer);
        }
    };

    const timer = setInterval(poll, 2000);
    poll();
});
</script>

@section('content')
    <div class="mb-6">
        <p class="text-sm text-zinc-600">Upload an XML file to import contacts.</p>
    </div>

    @if($importUuid !== null)
        <div id="upload-status" style="display: none;" data-import-uuid="{{ $importUuid }}">
            Loading...
        </div>
    @endif

    <div class="rounded-lg border border-zinc-200 bg-white p-6" id="import-form"  @if($importUuid !== null)style="display: none" @endif>
        <form method="post" action="{{ route('import.store') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="file" class="text-sm font-medium text-zinc-700">XML file</label>
                <div class="mt-2 flex items-center justify-between gap-4">
                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept=".xml,text/xml,application/xml"
                        class="block w-full flex-1 text-sm text-zinc-700 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                    />
                    <button type="submit" class="shrink-0 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Start import
                    </button>
                </div>
                @error('file')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </div>
@endsection
