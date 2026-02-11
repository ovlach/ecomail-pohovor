<?php

declare(strict_types=1);

namespace App\Services\SearchProvider;

use App\Data\Contact;
use Illuminate\Pagination\LengthAwarePaginator;

interface SearchProvider
{
    /**
     * @return LengthAwarePaginator<int, \App\Models\Contact>
     */
    public function search(string $query, int $paginate): LengthAwarePaginator;
}
