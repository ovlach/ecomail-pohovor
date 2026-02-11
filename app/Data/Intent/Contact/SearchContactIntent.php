<?php

declare(strict_types=1);

namespace App\Data\Intent\Contact;

final class SearchContactIntent
{
    private const QUERY_ALL = '*';

    public readonly string $query;
    public readonly int $resultsPerPage;

    public function __construct(
        string $query,
    ) {
        $this->query = strtolower($query);
        $this->resultsPerPage = 20;
    }

    public static function queryAll(): SearchContactIntent
    {
        return new SearchContactIntent(
            self::QUERY_ALL
        );
    }
}
