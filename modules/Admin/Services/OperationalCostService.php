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
use App\Helpers\ImageUploaderHelper;
use App\Models\FinanceTransaction;
use App\Models\OperationalCost;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OperationalCostService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
    ) {}

    public function duplicate($id)
    {
        return $this->find($id)->replicate();
    }

    public function find($id)
    {
        return OperationalCost::with(['financeAccount', 'category', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function findOrCreate($id = 0)
    {
        return $id ? OperationalCost::findOrFail($id) : new OperationalCost(['date' => date('Y-m-d')]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = OperationalCost::with(['category', 'financeAccount']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id']) && $filter['category_id'] !== 'all') {
            if ($filter['category_id'] === null) {
                $q->whereNull('category_id');
            } else if ($filter['category_id'] !== 'all') {
                $q->where('category_id', '=', $filter['category_id']);
            }
        }

        if (!empty($filter['finance_account_id']) && $filter['finance_account_id'] !== 'all') {
            $q->where('finance_account_id', '=', $filter['finance_account_id']);
        }

        if (!empty($filter['year']) && $filter['year'] !== 'null') {
            $q->whereYear('date', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'null') {
                $q->whereMonth('date', $filter['month']);
            }
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return  $q->paginate($options['per_page'])->withQueryString();
    }

    public function save(OperationalCost $item, array $validated, $newImage)
    {
        $newlyUploadedImagePath = null;
        $oldItem = null;
        $oldLogData = [];

        if (!empty($validated['id'])) {
            $oldLogData = $this->generateActivityLogData($item);
            $oldItem = clone $item;
        }

        $oldImagePath = $oldItem ? $oldItem->image_path : null;

        try {
            DB::beginTransaction();

            if ($newImage) {
                // Upload file baru dan hapus yang lama
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $newImage,
                    'operational-costs',
                    $oldImagePath
                );
                $validated['image_path'] = $newlyUploadedImagePath;
            } elseif ($validated['image_path'] === null) {
                // User hapus gambar lama, hapus dari storage
                if ($oldImagePath) {
                    ImageUploaderHelper::deleteImage($oldImagePath);
                }
                $validated['image_path'] = null;
            } else {
                // Pertahankan gambar lama, tidak perlu ubah validated
            }

            unset($validated['image']);

            $item->fill($validated);

            if (empty($item->getDirty())) {
                DB::rollBack();
                throw new ModelNotModifiedException();
            }

            $item->save();

            // 5. PENANGANAN TRANSAKSI KEUANGAN
            // Logika pengembalian saldo hanya perlu dilakukan jika ada perubahan akun
            $this->financeTransactionService->handleTransaction(
                [
                    'ref_id'     => $item->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'datetime'   => new Carbon($item->date),
                    'account_id' => $item->finance_account_id,
                    'amount'     => -abs($item->amount),
                    'type'       => FinanceTransaction::Type_Expense,
                    'notes'      => "Biaya operasional #$item->code",
                ],
                $oldItem ? [
                    'ref_id'     => $oldItem->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'account_id' => $oldItem->finance_account_id,
                    'amount'     => -abs($oldItem->amount)
                ] : []
            );

            $this->documentVersionService->createVersion($item);

            $item->load([
                'financeAccount',
                'category',
                'creator',
                'updater'
            ]);

            if (empty($validated['id'])) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCost_Create,
                    "Biaya operasional ID: $item->code telah dibuat.",
                    [
                        'formatter' => 'operational-cost',
                        'new_data' => $this->generateActivityLogData($item),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCost_Update,
                    "Biaya operasional ID: $item->code telah diperbarui.",
                    [
                        'formatter' => 'operational-cost',
                        'old_data' => $oldLogData,
                        'new_data' => $this->generateActivityLogData($item),
                    ]
                );
            }

            DB::commit();

            return $item;
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $e;
        }

        return false;
    }

    public function delete(OperationalCost $item)
    {
        DB::transaction(function () use ($item) {
            $item->delete();

            $this->financeTransactionService->reverseTransaction(
                $item->id,
                FinanceTransaction::RefType_OperationalCost
            );

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_OperationalCost,
                UserActivityLog::Name_OperationalCost_Delete,
                "Biaya operasional ID: $item->id telah dihapus.",
                [
                    'formatter' => 'operational-cost',
                    'data' => $this->generateActivityLogData($item),
                ]
            );

            ImageUploaderHelper::deleteImage($item->image_path);
            return $item;
        });
    }

    private function generateActivityLogData(OperationalCost $item)
    {
        $data = $item->getAttributes();
        $data['category_name']        = $item->category?->name;
        $data['created_by_username']  = $item->creator?->username;
        $data['updated_by_username']  = $item->updater?->username;
        $data['finance_account_name'] = $item->financeAccount?->name;
        return $data;
    }
}
