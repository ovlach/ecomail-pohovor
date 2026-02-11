<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Data\Command\BatchResult;
use App\Data\Contact;
use App\Data\Intent\Contact\CreateContactIntent;
use App\Data\InvariantException;
use App\Services\Mapper\ContactMapper;
use App\Services\Storage\ContactStorageProvider;

class CreateContactCommand
{
    public function __construct(
        private readonly ContactMapper $contactMapper,
        private readonly ContactStorageProvider $contactStorageProvider,
    ) {
    }

    public function execute(CreateContactIntent $command): ?Contact
    {
        $entity = $this->contactMapper->tryToCreateFromIntent($command);

        return $this->contactStorageProvider->create($entity);
    }

    /**
     * @param array<CreateContactIntent> $actualBatch
     * @return BatchResult
     */
    public function multipleExecute(array $actualBatch): BatchResult
    {
        $contacts = [];

        foreach ($actualBatch as $intent) {
            try {
                $entity = $this->contactMapper->tryToCreateFromIntent($intent);
                $contacts[] = $entity;
            } catch (InvariantException) {
                // Do nothing -> mark later as failed
            }
        }

        $result = $this->contactStorageProvider->batchCreate($contacts);

        return new BatchResult(
            count($actualBatch) - count($contacts),
            count($contacts) - $result,
            $result,
        );
    }
}
