<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Service\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\ServiceTechnician;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\DocumentVersionService;
use Modules\Admin\Services\UserActivityLogService;

class TechnicianService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService
    ) {}

    /**
     * Mencari teknisi berdasarkan ID dengan kolom spesifik
     */
    public function find(int $id, $cols = "*"): ServiceTechnician
    {
        $query = ServiceTechnician::query();

        if ($cols !== "*") {
            $selectedColumns = is_array($cols) ? $cols : explode(',', $cols);
            // Pastikan FK untuk relasi creator/updater tetap terbawa jika menggunakan trait Blameable
            $requiredFks = ['id', 'user_id', 'creator_id', 'updater_id'];
            $finalColumns = array_unique(array_merge($selectedColumns, $requiredFks));
            $query->select($finalColumns);
        }

        return $query->with([
            'user' => function ($q) {
                $q->select('id', 'name', 'username', 'email');
            },
            'creator' => function ($q) {
                $q->select('id', 'name', 'username');
            },
            'updater' => function ($q) {
                $q->select('id', 'name', 'username');
            }
        ])->findOrFail($id);
    }

    /**
     * Inisialisasi model baru atau cari yang sudah ada
     */
    public function findOrCreate($id): ServiceTechnician
    {
        return $id ? $this->find($id) : new ServiceTechnician([
            'active' => true,
            'code' => ServiceTechnician::generateCode(),
        ]);
    }

    /**
     * Mengambil data teknisi dengan paginasi dan filter
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'] ?? [];

        $q = ServiceTechnician::query()->select([
            'id',
            'code',
            'user_id',
            'name',
            'phone',
            'email',
            'address',
            'active'
        ]);

        // Filter Pencarian
        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $search = '%' . $filter['search'] . '%';
                $q->where('code', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('address', 'like', $search);
            });
        }

        // Filter Status Aktif
        if (isset($filter['status']) && ($filter['status'] === 'active' || $filter['status'] === 'inactive')) {
            $q->where('active', $filter['status'] === 'active');
        }

        $q->orderBy($options['order_by'] ?? 'name', $options['order_type'] ?? 'asc');

        return $q->paginate($options['per_page'] ?? 10)->withQueryString();
    }

    /**
     * Simpan atau Perbarui data teknisi
     */
    public function save(ServiceTechnician $item, array $data): ServiceTechnician
    {
        $oldData = $item->toArray();

        $item->fill($data);

        if ($item->exists && empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldData) {
            $isNew = !$item->exists;

            $item->save();

            // Versi dokumen untuk audit trail (Trait HasDocumentVersions)
            $this->documentVersionService->createVersion($item);

            $category = 'service.technician';
            $logName = $isNew ? 'service.technician.create' : 'service.technician.update';
            $actionWord = $isNew ? 'dibuat' : 'diperbarui';

            $logPayload = [
                'formatter' => 'service.technician',
                'new_data' => $item->toArray(),
            ];

            if (!$isNew) {
                $logPayload['old_data'] = $oldData;
            }

            $this->userActivityLogService->log(
                $category,
                $logName,
                "Teknisi ID: $item->id, $item->name telah $actionWord.",
                $logPayload
            );

            return $item;
        });
    }

    /**
     * Hapus teknisi (Soft Delete)
     */
    public function delete(ServiceTechnician $item): ServiceTechnician
    {
        return DB::transaction(function () use ($item) {
            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                'service.technician',
                'service.technician.delete',
                "Teknisi ID: $item->id, $item->name telah dihapus.",
                [
                    'formatter' => 'service.technician',
                    'data' => $item->toArray()
                ]
            );

            return $item;
        });
    }
}
