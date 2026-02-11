<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact;

final class CreateContactIntent
{
    public function __construct(
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
    ) {
    }
}
