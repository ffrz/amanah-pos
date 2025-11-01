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

namespace Modules\Admin\Services;

use App\Models\Product;
use App\Models\StockMovement;

use Modules\Admin\Http\Requests\StockMovement\GetDataRequest;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementService
{
    /**
     * Mengambil data pergerakan stok dengan pemfilteran dan paginasi.
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = StockMovement::with(['creator', 'product']);

        if (!empty($filter['product_id'])) {
            $q->where('product_id', $filter['product_id']);
        }

        if (!empty($filter['search'])) {
            $q->where(function (Builder $query) use ($filter) {
                $query->where('notes', 'like', '%' . $filter['search'] . '%');

                $query->orWhereHas('product', function ($productQuery) use ($filter) {
                    $productQuery->where('name', 'like', '%' . $filter['search'] . '%');
                });
            });
        }

        if (!empty($filter['ref_type']) && $filter['ref_type'] !== 'all') {
            $q->where('ref_type', $filter['ref_type']);
        }

        if (!empty($filter['start_date'])) {
            $q->whereDate('created_at', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('created_at', '<=', $filter['end_date'] . ' 23:59:59');
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function findByRef(int $ref_id, string $ref_type): StockMovement
    {
        $item = StockMovement::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        return $item;
    }

    public function deleteByRef(int $ref_id, string $ref_type): StockMovement
    {
        $item = $this->findByRef($ref_id, $ref_type);

        if ($item) {
            $item->delete();
        }

        return $item;
    }

    public function processStockIn(array $data)
    {
        return $this->processStockDelta($data, $data['quantity']);
    }

    public function processStockOut(array $data)
    {
        return $this->processStockDelta($data, -$data['quantity']);
    }

    private function processStockDelta(array $data, $quantity)
    {
        $item = StockMovement::create($data);

        $product = Product::where('id', $data['product_id'])->lockForUpdate()->firstOrFail();
        $product->stock += $quantity;
        $product->save();

        return $item;
    }
}
