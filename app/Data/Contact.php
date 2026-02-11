<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\UuidInterface;

final readonly class Contact
{
    public string $email;

    public function __construct(
        public UuidInterface $uuid,
        string $email,
        public ?string $firstName,
        public ?string $lastName,
    ) {
        $this->email = strtolower($email);

        $validator = Validator::make(['email' => $this->email], [
            'email' => ['required', 'email:rfc', 'max:254'],
        ]);

        if ($this->firstName !== null && strlen($this->firstName) > 100) {
            throw new InvariantException('First name cannot be longer than 100 characters', ['first_name']);
        }

        if ($this->lastName !== null && strlen($this->lastName) > 100) {
            throw new InvariantException('Last name cannot be longer than 100 characters', ['last_name']);
        }

        if ($validator->fails()) {
            throw new InvariantException($validator->errors()->first('email'), ['email']);
        }
    }
}
