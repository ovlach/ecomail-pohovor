<?php

declare(strict_types=1);

namespace App\Services\ProcessImportMonitor;

interface ProcessImportMonitor
{
    public function newImport(string $file): ProcessImportState&ProcessImportIdentifiable;
}
