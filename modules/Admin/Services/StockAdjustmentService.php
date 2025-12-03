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
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockMovement;
use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        protected DocumentVersionService $documentVersionService,
        protected UserActivityLogService $userActivityLogService,
    ) {}
    /**
     * Mengambil data pergerakan stok dengan pemfilteran dan paginasi.
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = StockAdjustment::with(['creator:id,username,name', 'updater:id,username,name']);

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', '=', $filter['type']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $q->where(function ($q) use ($search) {
                $q->orWhere('code', 'like', '%' . $search . '%');
                $q->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function find(int $id): StockAdjustment
    {
        return StockAdjustment::with(['creator', 'updater', 'details'])->findOrFail($id);
    }

    /**
     * @param $id int ID Stock Adjustment
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function getDetails(int $id)
    {
        return DB::table('stock_adjustment_details')
            ->join('products', 'stock_adjustment_details.product_id', '=', 'products.id')
            ->where('stock_adjustment_details.parent_id', $id)
            ->orderBy('stock_adjustment_details.id', 'asc')
            ->select(
                'stock_adjustment_details.id',
                'stock_adjustment_details.new_quantity',
                'stock_adjustment_details.notes',
                'products.id as product_id',
                'products.name as product_name',
                'products.stock as old_quantity',
                'products.uom',
            )
            ->get();
    }

    public function create(array $data): StockAdjustment
    {
        $products = Product::whereIn('id', $data['product_ids'])->get()->keyBy('id');

        return DB::transaction(function () use ($data, $products) {
            $item = new StockAdjustment([
                'datetime' => $data['datetime'],
                'status' => StockAdjustment::Status_Draft,
                'type' => $data['type'],
                'notes' => $data['notes'],
                'total_cost' => 0,
                'total_price' => 0,
            ]);
            $item->save();

            foreach ($data['product_ids'] as $product_id) {
                $product = $products[$product_id];
                $detail = new StockAdjustmentDetail([
                    'parent_id' => $item->id,
                    'product_id' => $product_id,
                    'product_name' => $product->name,
                    'old_quantity' => $product->stock,
                    'new_quantity' => $product->stock,
                    'balance' => 0,
                    'uom' => $product->uom,
                    'cost' => $product->cost,
                    'price' => $product->price_1,
                ]);
                $detail->save();
            }

            // TODO: Log Activity and create document version
            $this->userActivityLogService->log(
                UserActivityLog::Category_StockAdjustment,
                UserActivityLog::Name_StockAdjustment_Create,
                "Penyesuaian stok $item->code telah dibuat.",
                [
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    public function getProducts(): Collection
    {
        return Product::with(['category'])
            ->where(function ($q) {
                $q->where('type', Product::Type_Stocked);
            })
            ->where('active', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'description', 'barcode', 'category_id', 'supplier_id', 'type', 'stock', 'uom', 'cost', 'price_1']);
    }

    public function save(StockAdjustment $item, $validated)
    {
        $item->fill($validated);

        $details = $validated['details'];

        return DB::transaction(function () use ($item, $details) {
            $total_cost = 0;
            $total_price = 0;

            $stored_details = StockAdjustmentDetail::where('parent_id', $item->id)->get()->keyBy('id');

            foreach ($details as $d) {
                $detail = $stored_details[$d['id']];
                $detail->new_quantity = floatval($d['new_quantity']);
                $detail->balance = $detail->new_quantity - $detail->old_quantity;
                $detail->subtotal_cost = $detail->balance * $detail->cost;
                $detail->subtotal_price = $detail->balance * $detail->price;
                $detail->save();

                if ($item->status == StockAdjustment::Status_Closed) {
                    // update stok
                    DB::update('UPDATE products SET stock=? where id=?', [$detail->new_quantity, $detail->product_id]);

                    // simpan riwayat perubahan stok
                    $stockMovement = new StockMovement([
                        'parent_id' => $item->id,
                        'parent_ref_type' => StockMovement::ParentRefType_StockAdjustment,
                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product_name,
                        'uom' => $detail->uom,
                        'ref_id' => $detail->id,
                        'ref_type' => StockMovement::RefType_StockAdjustmentDetail,
                        'quantity' => $detail->balance,
                        'quantity_before' => $detail->old_quantity,
                        'quantity_after' => $detail->new_quantity,
                        'notes' => "Penyesuaian stok #$item->code",
                        'document_code' => $item->code,
                        'document_datetime' => $item->datetime,
                    ]);
                    $stockMovement->save();
                }

                $total_cost += $detail->subtotal_cost;
                $total_price += $detail->subtotal_price;
            }

            $item->total_cost = $total_cost;
            $item->total_price = $total_price;
            $item->save();

            if ($item->status === StockAdjustment::Status_Closed) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_StockAdjustment,
                    UserActivityLog::Name_StockAdjustment_Close,
                    "Penyesuaian stok $item->code telah selesai.",
                    [
                        'data' => $item->toArray(),
                    ]
                );

                $this->documentVersionService->createVersion($item);
            } else if ($item->status === StockAdjustment::Status_Cancelled) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_StockAdjustment,
                    UserActivityLog::Name_StockAdjustment_Cancel,
                    "Penyesuaian stok $item->code telah dibatalkan.",
                    [
                        'data' => $item->toArray(),
                    ]
                );

                $this->documentVersionService->createVersion($item);
            }

            return $item;
        });
    }

    public function delete(StockAdjustment $item): StockAdjustment
    {
        return DB::transaction(function () use ($item) {
            if ($item->status == StockAdjustment::Status_Closed) {
                $details = StockAdjustmentDetail::where('parent_id', $item->id)->get()->keyBy('product_id');

                $products = Product::whereIn('id', array_keys($details->all()))->get();

                foreach ($products as $product) {
                    $detail = $details[$product->id];
                    $product->stock += (-$detail->balance); // refund stok
                    $product->save();

                    // Hapus stock movement terkait detail ini
                    $deleted = DB::delete(
                        'DELETE FROM stock_movements WHERE ref_type = ? AND ref_id = ?',
                        [StockMovement::RefType_StockAdjustmentDetail, $detail->id]
                    );
                }
            }

            DB::delete('DELETE FROM stock_adjustment_details WHERE parent_id=?', [$item->id]);

            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_StockAdjustment,
                UserActivityLog::Name_StockAdjustment_Delete,
                "Penyesuaian stok $item->code telah dihapus.",
                [
                    'data' => $item->toArray(),
                ]
            );

            $this->documentVersionService->createVersion($item);

            return $item;
        });
    }
}
