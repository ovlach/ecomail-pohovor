<?php

declare(strict_types=1);

namespace App\Services\Query;

use App\Data\Intent\Contact\SearchContactIntent;
use App\Data\Intent\Contact\SearchContactIntentByUuid;
use App\Models\Contact;
use App\Services\Mapper\ContactMapper;
use App\Services\SearchProvider\SearchProvider;
use App\Services\Storage\ContactStorageProvider;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchContactQuery
{
    public function __construct(
        private readonly SearchProvider $searchProvider,
        private readonly ContactStorageProvider $contactStorageProvider,
        private readonly ContactMapper $contactMapper,
    ) {
    }

    public function executeOne(SearchContactIntentByUuid $search): ?\App\Data\Contact
    {
        $modelResult = $this->contactStorageProvider->fetchByUuid($search->uuid);
        if ($modelResult === null) {
            return null;
        }

        return $this->contactMapper->fromModel($modelResult);
    }

    /**
     * @return LengthAwarePaginator<int, \App\Data\Contact>
     */
    public function execute(SearchContactIntent $searchIntent): LengthAwarePaginator
    {
        if ($searchIntent->query == SearchContactIntent::queryAll()->query) {
            /** @var LengthAwarePaginator<int, Contact> $modelResult */
            $modelResult = $this->contactStorageProvider->fetchPaginatedAll($searchIntent->resultsPerPage);
        } else {
            /** @var LengthAwarePaginator<int, Contact> $modelResult */
            $modelResult = $this->searchProvider->search($searchIntent->query, $searchIntent->resultsPerPage);
        }

        $collection = $modelResult->getCollection()->map(
            fn (Contact $contact) => $this->contactMapper->fromModel($contact),
        );

        return new LengthAwarePaginator(
            $collection,
            $modelResult->total(),
            $modelResult->perPage(),
            $modelResult->currentPage(),
            $modelResult->getOptions(),
        );
    }
}
