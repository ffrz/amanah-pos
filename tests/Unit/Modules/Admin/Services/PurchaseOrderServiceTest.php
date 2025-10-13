<?php

namespace Tests\Unit\Modules\Admin\Services;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Modules\Admin\Services\PurchaseOrderService;

class PurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PurchaseOrderService $purchaseOrderService;

    // Siapkan service sebelum setiap test
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->purchaseOrderService = $this->app->make(PurchaseOrderService::class);
    }
}
