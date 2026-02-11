<?php

declare(strict_types=1);

namespace App\Providers;

use App\Data\Contact;
use App\Data\ContactImport;
use App\Data\Intent\Contact\SearchContactIntentByUuid;
use App\Data\Intent\ProcessImport\SearchImportContactsIntent;
use App\Services\Query\ImportContactsStateQuery;
use App\Services\Query\SearchContactQuery;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;

class RouteBindingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::bind('contact', function (string $value): Contact {
            if (! Uuid::isValid($value)) {
                abort(404);
            }

            $searchIntent = new SearchContactIntentByUuid(Uuid::fromString($value));
            $contact = app(SearchContactQuery::class)->executeOne($searchIntent);

            if ($contact === null) {
                abort(404);
            }

            return $contact;
        });

        Route::bind('contactImport', function (string $value): ContactImport {
            if (! Uuid::isValid($value)) {
                abort(404);
            }

            $searchIntent = new SearchImportContactsIntent(Uuid::fromString($value));
            $contactImport = app(ImportContactsStateQuery::class)->executeOne($searchIntent);

            if ($contactImport === null) {
                abort(404);
            }

            return $contactImport;
        });
    }
}
