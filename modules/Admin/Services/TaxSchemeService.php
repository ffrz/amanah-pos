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
use App\Models\TaxScheme;
use App\Models\UserActivityLog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class TaxSchemeService
 */
class TaxSchemeService
{
    /**
     * @param UserActivityLogService $userActivityLogService Service untuk mencatat aktivitas pengguna.
     */
    public function __construct(
        protected UserActivityLogService $userActivityLogService
    ) {}

    /**
     * Mengambil data skema pajak produk dengan pemfilteran dan paginasi.
     *
     * @param array $filter Array berisi kriteria filter, seperti ['search' => 'kata_kunci'].
     * @param string $orderBy Kolom yang digunakan untuk mengurutkan data (e.g., 'name', 'created_at').
     * @param string $orderType Tipe urutan ('asc' atau 'desc').
     * @param int $perPage Jumlah item per halaman untuk paginasi.
     * @return LengthAwarePaginator
     */
    public function getData($options): LengthAwarePaginator
    {
        $q = TaxScheme::query();

        $filter = $options['filter'];

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Mencari skema pajak berdasarkan ID.
     *
     * @param int $id ID skema pajak.
     * @return TaxScheme
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): TaxScheme
    {
        return TaxScheme::findOrFail($id);
    }

    /**
     * Mencari skema pajak produk berdasarkan ID.
     *
     * @param int $id ID Skema Pajak Produk.
     * @return TaxScheme
     * @throws ModelNotFoundException
     */
    public function findOrCreate($id): TaxScheme
    {
        return $id ? $this->findOrFail($id) : new TaxScheme();
    }

    /**
     * Menduplikasi skema pajak produk yang sudah ada.
     *
     * @param int $id ID skema pajak yang akan diduplikasi.
     * @return TaxScheme Model TaxScheme baru yang sudah diinisialisasi untuk pembuatan.
     * @throws ModelNotFoundException
     */
    public function duplicate(int $id): TaxScheme
    {
        return $this->findOrFail($id)->replicate();
    }

    /**
     * Menyimpan (membuat atau memperbarui) skema pajak produk.
     *
     * @param TaxScheme $item Model TaxScheme yang sudah diinisialisasi atau dicari.
     * @param array $data Data tervalidasi dari SaveRequest untuk mengisi model.
     * @return mixed
     * @throws Throwable Jika transaksi database gagal.
     */
    public function save(TaxScheme $item, array $data): mixed
    {
        $oldData = $item->getAttributes();
        $isNew   = !$item->id;

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldData, $isNew) {
            $item->save();

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_TaxScheme,
                    UserActivityLog::Name_TaxScheme_Create,
                    "Skema Pajak $item->name telah ditambahkan.",
                    [
                        'formatter' => 'tax-scheme',
                        'data' => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_TaxScheme,
                    UserActivityLog::Name_TaxScheme_Update,
                    "Skema Pajak $item->name telah diperbarui.",
                    [
                        'formatter' => 'tax-scheme',
                        'old_data' => $oldData,
                        'new_data' => $item->getAttributes(),
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus skema pajak produk.
     *
     * @param TaxScheme $item Model TaxScheme yang akan dihapus.
     * @return mixed 
     * @throws Throwable Jika transaksi database gagal.
     */
    public function delete(TaxScheme $item)
    {
        return DB::transaction(function () use ($item) {
            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_TaxScheme,
                UserActivityLog::Name_TaxScheme_Delete,
                "Skema Pajak $item->name telah dihapus.",
                [
                    'formatter' => 'tax-scheme',
                    'data' => $item->getAttributes(),
                ]
            );

            return $item;
        });
    }
}
