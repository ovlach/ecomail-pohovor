<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Ramsey\Uuid\UuidInterface;

class DatabaseImportStorageProvider implements ProcessImportStorageProvider
{
    public function fetchByUuid(UuidInterface $uuid): ?\App\Models\ContactImport
    {
        return \App\Models\ContactImport::query()->find($uuid->toString());
    }
}
