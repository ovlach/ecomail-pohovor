<?php

declare(strict_types=1);

namespace App\Data\Intent\ProcessImport;

class ImportContactsIntent
{
    public function __construct(
        public readonly string $path
    ) {
    }
}
