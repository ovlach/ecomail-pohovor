<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\Carbon;

class ContactImport
{
    public function __construct(
        public readonly string $id,
        public readonly Carbon $queueAt,
        public readonly ?Carbon $startedAt,
        public readonly ?Carbon $finishedAt,
        public readonly int $totalProcessed,
        public readonly int $errors,
        public readonly int $duplicates,
        public readonly string $state,
        public readonly ?string $file,
    ) {
    }
}
