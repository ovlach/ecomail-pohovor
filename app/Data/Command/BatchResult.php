<?php

declare(strict_types=1);

namespace App\Data\Command;

class BatchResult
{
    public function __construct(
        public readonly int $fails,
        public readonly int $duplicates,
        public readonly int $success,
    ) {
    }
}
