<?php

declare(strict_types=1);

namespace App\Services\Query;

use App\Data\ContactImport;
use App\Data\Intent\ProcessImport\SearchImportContactsIntent;
use App\Services\Mapper\ContactImportMapper;
use App\Services\Storage\ProcessImportStorageProvider;

class ImportContactsStateQuery
{
    public function __construct(
        private readonly ContactImportMapper $contactImportMapper,
        private readonly ProcessImportStorageProvider $processImportStorageProvider,
    ) {
    }

    public function executeOne(SearchImportContactsIntent $search): ?ContactImport
    {
        $modelResult = $this->processImportStorageProvider->fetchByUuid($search->uuid);
        if ($modelResult === null) {
            return null;
        }

        return $this->contactImportMapper->fromModel($modelResult);
    }
}
