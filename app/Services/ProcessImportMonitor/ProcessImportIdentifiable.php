<?php

declare(strict_types=1);

namespace App\Services\ProcessImportMonitor;

use Ramsey\Uuid\UuidInterface;

interface ProcessImportIdentifiable
{
    public UuidInterface $uuid { get; }
}
