<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentProductCategoryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan binding Repository.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductCategoryRepositoryInterface::class,
            EloquentProductCategoryRepository::class
        );

        // Tambahkan binding repository lain di sini di masa mendatang:
        /*
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
        */
    }

    /**
     * Bootstrapping services.
     */
    public function boot(): void
    {
        //
    }
}
