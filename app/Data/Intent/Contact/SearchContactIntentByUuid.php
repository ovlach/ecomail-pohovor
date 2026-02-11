<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact;

use Ramsey\Uuid\UuidInterface;

class SearchContactIntentByUuid
{
    public function __construct(
        public readonly UuidInterface $uuid,
    ) {
    }
}
