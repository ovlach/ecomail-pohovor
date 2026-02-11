<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ProcessImportMonitor\DatabaseProcessImportMonitor;
use App\Services\ProcessImportMonitor\ProcessImportMonitor;
use App\Services\SearchProvider\DatabaseSearchProvider;
use App\Services\SearchProvider\SearchProvider;
use App\Services\Storage\ContactStorageProvider;
use App\Services\Storage\DatabaseContactStorageProvider;
use App\Services\Storage\DatabaseImportStorageProvider;
use App\Services\Storage\ProcessImportStorageProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SearchProvider::class,
            DatabaseSearchProvider::class,
        );
        $this->app->bind(
            ContactStorageProvider::class,
            DatabaseContactStorageProvider::class
        );
        $this->app->bind(
            ProcessImportStorageProvider::class,
            DatabaseImportStorageProvider::class,
        );
        $this->app->bind(
            ProcessImportMonitor::class,
            DatabaseProcessImportMonitor::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('contacts.*', function (\Illuminate\View\View $view): void {
            $q = request()->query('q');
            $searchQuery = is_string($q) ? $q : null;
            $searchQueryParams = $searchQuery !== null && $searchQuery !== '' ? ['q' => $searchQuery] : [];

            $view->with('searchQuery', $searchQuery);
            $view->with('searchQueryParams', $searchQueryParams);
        });
    }
}
