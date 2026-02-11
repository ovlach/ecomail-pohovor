<?php

declare(strict_types=1);

namespace App\Services\ProcessImportMonitor;

use Ramsey\Uuid\Rfc4122\UuidV7;

class DatabaseProcessImportMonitor implements ProcessImportMonitor
{
    public function newImport(string $file): ProcessImportState&ProcessImportIdentifiable
    {
        return new DatabaseProcessImportState(UuidV7::uuid7(), $file);
    }
}
