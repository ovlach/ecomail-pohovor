<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact;

use Ramsey\Uuid\UuidInterface;

final class UpdateContactIntent
{
    public function __construct(
        public UuidInterface $uuid,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
    ) {
    }
}
