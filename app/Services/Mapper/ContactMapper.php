<?php

declare(strict_types=1);

namespace App\Services\Mapper;

use App\Data\Contact as ContactData;
use App\Data\Intent\Contact\CreateContactIntent;
use App\Data\Intent\Contact\UpdateContactIntent;
use App\Models\Contact;
use Ramsey\Uuid\Uuid;

final class ContactMapper
{
    public function fromModel(Contact $contact): ContactData
    {
        return new ContactData(
            // @phpstan-ignore-next-line argument.type
            Uuid::fromString((string) $contact->id),
            // @phpstan-ignore-next-line argument.type
            $contact->email,
            // @phpstan-ignore-next-line argument.type
            $contact->first_name,
            // @phpstan-ignore-next-line argument.type
            $contact->last_name,
        );
    }

    public function tryToCreateFromIntent(UpdateContactIntent|CreateContactIntent $intent): ContactData
    {
        if ($intent instanceof CreateContactIntent) {
            $uuid = Uuid::uuid7();
        } else {
            $uuid = $intent->uuid;
        }

        return new ContactData(
            $uuid,
            $intent->email,
            $intent->firstName,
            $intent->lastName,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toModelAttributes(
        ContactData $contactData,
    ): array {
        return [
            'id' => $contactData->uuid->toString(),
            'first_name' => $contactData->firstName,
            'last_name' => $contactData->lastName,
            'email' => $contactData->email,
        ];
    }
}
