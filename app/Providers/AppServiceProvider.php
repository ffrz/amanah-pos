<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Providers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        view()->composer('*', function ($view) {
            $modules = config('app-modules');

            // Ambil segmen pertama dari URL
            $firstSegment = Request::segment(1);

            // Cari modul berdasarkan prefix
            $activeModule = collect($modules)
                ->first(fn($m) => $m['prefix'] === $firstSegment);

            $view->with([
                'MODULE_NAME' => $activeModule['name'] ?? '',
                'MODULE_DISPLAY_NAME' => $activeModule['display_name'] ?? '',
            ]);
        });

        Blueprint::macro('createdUpdatedDeletedTimestamps', function () {
            /** @var Blueprint $this */
            $this->dateTime('created_at')->nullable();
            $this->dateTime('updated_at')->nullable();
            $this->dateTime('deleted_at')->nullable();

            $this->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
        });

        Blueprint::macro('createdUpdatedTimestamps', function () {
            /** @var Blueprint $this */
            $this->dateTime('created_at')->nullable();
            $this->dateTime('updated_at')->nullable();

            $this->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });

        Blueprint::macro('createdDeletedTimestamps', function () {
            /** @var Blueprint $this */
            $this->dateTime('created_at')->nullable();
            $this->dateTime('deleted_at')->nullable();

            $this->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $this->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
        });

        Blueprint::macro('createdTimestamps', function () {
            /** @var Blueprint $this */
            $this->dateTime('created_at')->nullable();
            $this->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }
}
