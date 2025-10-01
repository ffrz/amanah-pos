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
    public function getPaginatedData(GetDataRequest $request): LengthAwarePaginator
    {
        $validated = $request->validated();
        $filter = $validated['filter'];

        $q = StockMovement::with(['creator', 'product']);

        // 1. Filter berdasarkan Product ID (langsung dari parameter)
        if (!empty($validated['product_id'])) {
            $q->where('product_id', $validated['product_id']);
        }

        // 2. Filter Search
        if (!empty($filter['search'])) {
            $q->where(function (Builder $query) use ($filter) {
                // Cari berdasarkan notes
                $query->where('notes', 'like', '%' . $filter['search'] . '%');

                // Cari berdasarkan nama produk (menggunakan whereHas)
                $query->orWhereHas('product', function ($productQuery) use ($filter) {
                    $productQuery->where('name', 'like', '%' . $filter['search'] . '%');
                });
            });
        }

        // 3. Filter Ref Type
        if (!empty($filter['ref_type']) && $filter['ref_type'] !== 'all') {
            $q->where('ref_type', $filter['ref_type']);
        }

        // 4. Filter Tahun dan Bulan
        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('created_at', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('created_at', $filter['month']);
            }
        }

        // 5. Ordering
        $q->orderBy($validated['order_by'], $validated['order_type']);

        // 6. Paginasi
        return $q->paginate($validated['per_page'])->withQueryString();
    }
}
