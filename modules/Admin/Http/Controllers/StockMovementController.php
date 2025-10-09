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

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Modules\Admin\Http\Requests\StockMovement\GetDataRequest;
use Modules\Admin\Services\StockMovementService;

class StockMovementController extends Controller
{

    public function __construct(
        protected StockMovementService $stockMovementService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', StockMovement::class);

        return inertia('stock-movement/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', StockMovement::class);

        $items = $this->stockMovementService->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }
}
