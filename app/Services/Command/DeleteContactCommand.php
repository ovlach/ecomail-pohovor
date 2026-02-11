<?php

declare(strict_types=1);

namespace App\Services\Command;

use App\Data\Intent\Contact\DeleteContactIntent;
use App\Services\Storage\ContactStorageProvider;

class DeleteContactCommand
{
    public function __construct(
        private readonly ContactStorageProvider $contactStorageProvider,
    ) {
    }

    public function execute(DeleteContactIntent $command): bool
    {
        return $this->contactStorageProvider->delete($command->uuid);
    }
}
