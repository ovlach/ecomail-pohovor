<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Data\Intent\ProcessImport\ImportContactsIntent;
use App\Jobs\ProcessImport;
use App\Services\ProcessImportMonitor\ProcessImportIdentifiable;
use App\Services\ProcessImportMonitor\ProcessImportMonitor;

class ImportContactsCommand
{
    public function __construct(private readonly ProcessImportMonitor $importMonitor)
    {
    }

    public function execute(ImportContactsIntent $command): ProcessImportIdentifiable
    {
        $import = $this->importMonitor->newImport($command->path);
        ProcessImport::dispatch($command->path, $import);

        return $import;
    }
}
