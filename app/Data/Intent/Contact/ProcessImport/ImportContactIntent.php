<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact\ProcessImport;

final class ImportContactIntent
{
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
    }
}
