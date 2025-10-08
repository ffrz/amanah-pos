<?php

namespace Modules\Admin\Services;

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

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('created_at', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('created_at', $filter['month']);
            }
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }
}
