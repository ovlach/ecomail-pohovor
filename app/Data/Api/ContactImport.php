<?php

declare(strict_types=1);

namespace App\Data\Api;

final class ContactImport
{
    public function __construct(
        public readonly string $id,
        public readonly string $queueAt,
        public readonly ?string $startedAt,
        public readonly ?string $finishedAt,
        public readonly int $totalProcessed,
        public readonly int $errors,
        public readonly int $duplicates,
        public readonly string $state,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'queue_at' => $this->queueAt,
            'started_at' => $this->startedAt,
            'finished_at' => $this->finishedAt,
            'total_processed' => $this->totalProcessed,
            'errors' => $this->errors,
            'duplicates' => $this->duplicates,
            'state' => $this->state,
        ];
    }
}
