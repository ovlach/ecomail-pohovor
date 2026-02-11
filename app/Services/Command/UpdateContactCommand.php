<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Data\Contact;
use App\Data\Intent\Contact\UpdateContactIntent;
use App\Services\Mapper\ContactMapper;
use App\Services\Storage\ContactStorageProvider;

class UpdateContactCommand
{
    public function __construct(
        private readonly ContactMapper $contactMapper,
        private readonly ContactStorageProvider $contactStorageProvider,
    ) {
    }

    public function execute(UpdateContactIntent $command): ?Contact
    {
        $entity = $this->contactMapper->tryToCreateFromIntent($command);

        return $this->contactStorageProvider->update($entity);
    }
}
