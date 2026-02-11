<?php

declare(strict_types=1);

namespace App\Services\Mapper;

use App\Data\ContactImport as ContactImportData;
use App\Models\ContactImport;

final class ContactImportMapper
{
    public function fromModel(ContactImport $contactImport): ContactImportData
    {
        return new ContactImportData(
            $contactImport->id,
            $contactImport->queue_at,
            $contactImport->started_at,
            $contactImport->finished_at,
            (int) $contactImport->total_processed,
            (int) $contactImport->errors,
            (int) $contactImport->duplicates,
            $contactImport->state->value,
            $contactImport->file,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(ContactImportData $contactImport): array
    {
        return [
            'id' => $contactImport->id,
            'queue_at' => $contactImport->queueAt,
            'started_at' => $contactImport->startedAt,
            'finished_at' => $contactImport->finishedAt,
            'total_processed' => $contactImport->totalProcessed,
            'errors' => $contactImport->errors,
            'duplicates' => $contactImport->duplicates,
            'state' => $contactImport->state,
            'file' => $contactImport->file,
        ];
    }
}
