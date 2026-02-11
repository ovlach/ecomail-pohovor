<?php

declare(strict_types=1);

namespace App\Services\SearchProvider;

use App\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;

final class DatabaseSearchProvider implements SearchProvider
{
    /**
     * @return LengthAwarePaginator<int, \App\Models\Contact>
     */
    public function search(string $query, int $paginate): LengthAwarePaginator
    {
        return Contact::query()
            ->select('*')
            ->selectRaw(
                "CASE
                            WHEN email LIKE CONCAT('%', ?::text, '%')
                                THEN 1.0
                            ELSE ts_rank(ts_name, plainto_tsquery('simple', ?::text))
                        END AS rank",
                [$query, $query]
            )
            ->whereRaw(
                "ts_name @@ plainto_tsquery('simple', ?::text) OR email LIKE CONCAT('%', ?::text, '%')",
                [$query, $query]
            )
            ->orderByDesc('rank')
            ->paginate($paginate);
    }
}
