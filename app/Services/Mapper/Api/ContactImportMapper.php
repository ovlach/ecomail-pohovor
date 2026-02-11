<?php

declare(strict_types=1);

namespace App\Services\Mapper\Api;

use App\Data\Api\ContactImport as ContactImportData;
use App\Data\ContactImport;

final class ContactImportMapper
{
    public function fromModel(ContactImport $contactImport): ContactImportData
    {
        return new ContactImportData(
            $contactImport->id,
            $contactImport->queueAt->toIso8601String(),
            $contactImport->startedAt?->toIso8601String(),
            $contactImport->finishedAt?->toIso8601String(),
            (int) $contactImport->totalProcessed,
            (int) $contactImport->errors,
            (int) $contactImport->duplicates,
            $contactImport->state,
        );
    }
}
