<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ContactImport;
use App\Data\Intent\ProcessImport\ImportContactsIntent;
use App\Http\Requests\ImportContactsRequest;
use App\Services\Command\ImportContactsCommand;
use App\Services\Mapper\Api\ContactImportMapper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

class ImportController extends Controller
{
    public function __construct(
        private readonly ImportContactsCommand $command,
        private readonly ContactImportMapper $apiMapper,
    ) {
    }

    public function index(): View
    {
        return view('import.show', [
            'importUuid' => session('importUuid'),
        ]);
    }

    public function store(ImportContactsRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            abort(422, 'Missing import file.');
        }

        $path = $file->store('imports');
        if ($path === false) {
            abort(500, 'Failed to store import file.');
        }

        $import = new ImportContactsIntent(
            $path
        );

        $process = $this->command->execute($import);

        return redirect()
            ->route('import.index')
            ->with('status', 'Import queued.')
            ->with('importUuid', $process->uuid);
    }

    public function show(ContactImport $contactImport): JsonResponse
    {
        return response()->json($this->apiMapper->fromModel($contactImport)->toArray());
    }
}
