<?php

declare(strict_types=1);

namespace App\Data\Intent\ProcessImport;

use Ramsey\Uuid\UuidInterface;

class SearchImportContactsIntent
{
    public function __construct(
        public readonly UuidInterface $uuid,
    ) {
    }
}
