<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Data\Contact;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Uuid\UuidInterface;

interface ContactStorageProvider
{
    public function create(Contact $contact): ?Contact;

    public function update(Contact $contact): ?Contact;

    public function delete(UuidInterface $uuid): bool;

    /**
     * @param  array<Contact>  $intents
     */
    public function batchCreate(array $intents): int;

    /**
     * @return LengthAwarePaginator<int, \App\Models\Contact>
     */
    public function fetchPaginatedAll(int $paginate): LengthAwarePaginator;

    public function fetchByUuid(UuidInterface $uuid): ?\App\Models\Contact;
}
