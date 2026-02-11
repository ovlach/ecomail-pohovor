<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Data\Contact;
use App\Services\Mapper\ContactMapper;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Uuid\UuidInterface;

class DatabaseContactStorageProvider implements ContactStorageProvider
{
    public function __construct(
        private readonly ContactMapper $contactMapper,
    ) {
    }

    public function create(Contact $contact): ?Contact
    {
        $contact = \App\Models\Contact::query()->create(
            $this->contactMapper->toModelAttributes($contact),
        );

        return $this->contactMapper->fromModel($contact);
    }

    public function update(Contact $contact): ?Contact
    {
        $existingContact = \App\Models\Contact::query()->findOrFail($contact->uuid->toString());
        $result = $existingContact->update($this->contactMapper->toModelAttributes($contact));
        if ($result === false) {
            return null;
        }

        return $this->contactMapper->fromModel($existingContact);
    }

    public function delete(UuidInterface $uuid): bool
    {
        $contact = \App\Models\Contact::query()->findOrFail($uuid->toString());

        return (bool) $contact->delete();
    }

    /**
     * @param  array<Contact>  $intents
     */
    public function batchCreate(array $intents): int
    {
        return \App\Models\Contact::query()->insertOrIgnore(
            array_map(function (Contact $intent): array {
                return $this->contactMapper->toModelAttributes($intent);
            }, $intents)
        );
    }

    /**
     * @return LengthAwarePaginator<int, \App\Models\Contact>
     */
    public function fetchPaginatedAll(int $paginate): LengthAwarePaginator
    {
        return \App\Models\Contact::query()
            ->orderBy('id')
            ->paginate($paginate);
    }

    public function fetchByUuid(UuidInterface $uuid): ?\App\Models\Contact
    {
        return \App\Models\Contact::query()->find($uuid->toString());
    }
}
