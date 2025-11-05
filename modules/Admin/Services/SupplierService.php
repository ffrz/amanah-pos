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

use App\Exceptions\ModelNotModifiedException;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService
    ) {}

    public function addToBalance(Supplier $supplier, $balance)
    {
        $lockedSupplier = Supplier::where('id', $supplier->id)->lockForUpdate()->firstOrFail();
        $lockedSupplier->balance += $balance;
        $lockedSupplier->save();
        return $lockedSupplier;
    }

    public function find(int $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    public function findOrCreate($id): Supplier
    {
        return $id ? $this->find($id) : new Supplier([
            'active' => true,
            'code' => $this->generateSupplierCode(),
        ]);
    }

    public function duplicate(int $id): Supplier
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan (membuat atau memperbarui) data Pemasok.
     */
    public function save(Supplier $item, array $data): Supplier
    {
        $oldData = $item->toArray();

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldData) {
            $isNew = !$item->id;

            $item->save();

            $this->documentVersionService->createVersion($item);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Create,
                    "Pemasok ID: $item->id, $item->name telah dibuat.",
                    [
                        'formatter' => 'supplier',
                        'data' => $item->toArray()
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Update,
                    "Pemasok ID: $item->id, $item->name telah diperbarui.",
                    [
                        'formatter' => 'supplier',
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus Pemasok.
     */
    public function delete(Supplier $item)
    {
        return DB::transaction(function () use ($item) {

            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Supplier,
                UserActivityLog::Name_Supplier_Delete,
                "Pemasok ID: $item->id, $item->name telah dihapus.",
                [
                    'formatter' => 'supplier',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    /**
     * Logika untuk mem-filter dan mengambil data Supplier.
     */
    public function getData(array $options)
    {
        $filter = $options['filter'];

        $q = Supplier::query()->select([
            'id',
            'code',
            'name',
            // 'wallet_balance',
            'balance',
            'phone_1',
            'address',
            'active'
        ]);;

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->orWhere('code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone_1', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    /**
     * TODO: Pindahkan ke SaveRequest
     * Normalisasi data dari request (Memindahkan logika pengisian nilai default).
     */
    protected function normalizeData(array $data): array
    {
        $keys = [
            'phone_1',
            'phone_2',
            'phone_3',
            'address',
            'return_address',
            'bank_account_name_1',
            'bank_account_number_1',
            'bank_account_holder_1',
            'bank_account_name_2',
            'bank_account_number_2',
            'bank_account_holder_2',
            'url_1',
            'url_2',
            'notes'
        ];

        foreach ($keys as $key) {
            $data[$key] = $data[$key] ?? '';
        }

        return $data;
    }

    public function generateSupplierCode(): string
    {
        return Supplier::generateCode();
    }
}
