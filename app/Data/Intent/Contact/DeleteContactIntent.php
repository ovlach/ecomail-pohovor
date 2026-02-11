<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact;

use Ramsey\Uuid\UuidInterface;

final class DeleteContactIntent
{
    public function __construct(
        public UuidInterface $uuid,
    ) {
    }
}
