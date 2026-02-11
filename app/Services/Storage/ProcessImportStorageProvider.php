<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Models\ContactImport;
use Ramsey\Uuid\UuidInterface;

interface ProcessImportStorageProvider
{
    public function fetchByUuid(UuidInterface $uuid): ?ContactImport;
}
