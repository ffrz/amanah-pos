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

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\ImageUploaderHelper;
use App\Models\CashierCashDrop;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashierCashDropService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
    ) {}

    public function find($id)
    {
        return CashierCashDrop::with([
            'cashier',
            'sourceFinanceAccount',
            'targetFinanceAccount',
            'approver'
        ])->findOrFail($id);
    }

    public function findOrCreate($id = 0)
    {
        return $id ? CashierCashDrop::findOrFail($id) : new CashierCashDrop(['datetime' => now()]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = CashierCashDrop::with([
            'cashier:id,name',
            'sourceFinanceAccount:id,name,bank,number',
            'targetFinanceAccount:id,name,bank,number',
        ]);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        if (!empty($filter['cashier_id']) && $filter['cashier_id'] != 'all') {
            $q->where('cashier_id', $filter['cashier_id']);
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Membuat pengajuan Cash Drop baru (oleh Kasir)
     * Menggunakan mekanisme upload gambar yang sama dengan OperationalCostService
     */
    public function create(array $validated, $newImage)
    {
        return DB::transaction(function () use ($validated, $newImage) {
            $validated['status'] = CashierCashDrop::Status_Pending;

            if ($newImage) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $newImage,
                    'cashier-cash-drops'
                );
            }

            // Hapus image dari array validated agar tidak error saat create (jika logic upload manual)
            // Tapi karena kita passing $validated ke create(), pastikan key 'image' sudah di-unset di controller
            // atau kita unset disini untuk keamanan
            unset($validated['image']);

            $item = CashierCashDrop::create($validated);

            $this->documentVersionService->createVersion($item);

            $item->load(['cashier', 'sourceFinanceAccount', 'targetFinanceAccount']);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierCashDrop,
                UserActivityLog::Name_CashierCashDrop_Create,
                "Pengajuan setoran kas #$item->code dibuat.",
                [
                    'formatter' => 'cashier-cash-drop',
                    'new_data' => $this->generateActivityLogData($item)
                ]
            );

            return $item;
        });
    }

    /**
     * Mengkonfirmasi (Approve/Reject) Cash Drop (oleh Supervisor)
     */
    public function confirm(CashierCashDrop $item, $status, $approverId)
    {
        $oldLogData = $this->generateActivityLogData($item);

        return DB::transaction(function () use ($item, $status, $approverId, $oldLogData) {
            $item->status = $status;
            $item->approved_by = $approverId;
            $item->approved_at = Carbon::now();
            $item->save();

            if ($item->status === CashierCashDrop::Status_Approved) {
                // 1. Transaksi KELUAR dari Akun Sumber (Source) -> Expense
                $this->financeTransactionService->handleTransaction([
                    'datetime' => $item->datetime,
                    'account_id' => $item->source_finance_account_id,
                    'amount' => -$item->amount, // Negatif karena keluar
                    'type' => FinanceTransaction::Type_Expense,
                    'notes' => 'Setoran kas keluar ke ' . $item->targetFinanceAccount->name . ' Ref: #' . $item->code,
                    'ref_type' => FinanceTransaction::RefType_CashierCashDrop,
                    'ref_id' => $item->id,
                ]);

                // 2. Transaksi MASUK ke Akun Tujuan (Target) -> Income
                $this->financeTransactionService->handleTransaction([
                    'datetime' => $item->datetime,
                    'account_id' => $item->target_finance_account_id,
                    'amount' => $item->amount, // Positif karena masuk
                    'type' => FinanceTransaction::Type_Income,
                    'notes' => 'Terima setoran kas dari ' . $item->sourceFinanceAccount->name . ' Ref: #' . $item->code,
                    'ref_type' => FinanceTransaction::RefType_CashierCashDrop,
                    'ref_id' => $item->id,
                ]);

                $this->userActivityLogService->log(
                    UserActivityLog::Category_CashierCashDrop,
                    UserActivityLog::Name_CashierCashDrop_Approve,
                    "Setoran kas #$item->code telah disetujui.",
                    [
                        'formatter' => 'cashier-cash-drop',
                        'old_data' => $oldLogData,
                        'new_data' => $this->generateActivityLogData($item)
                    ]
                );
            } else if ($status === CashierCashDrop::Status_Rejected) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_CashierCashDrop,
                    UserActivityLog::Name_CashierCashDrop_Reject,
                    "Setoran kas #$item->code telah ditolak.",
                    [
                        'formatter' => 'cashier-cash-drop',
                        'old_data' => $oldLogData,
                        'new_data' => $this->generateActivityLogData($item)
                    ]
                );
            }

            $this->documentVersionService->createVersion($item);

            return $item;
        });
    }

    public function delete(CashierCashDrop $item)
    {
        return DB::transaction(function () use ($item) {
            // Reversal Jurnal Keuangan jika sudah Approved
            if ($item->status === CashierCashDrop::Status_Approved) {
                $this->financeTransactionService->reverseTransaction(
                    $item->id,
                    FinanceTransaction::RefType_CashierCashDrop
                );
            }

            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierCashDrop,
                UserActivityLog::Name_CashierCashDrop_Delete,
                "Setoran kas #$item->code telah dihapus.",
                [
                    'formatter' => 'cashier-cash-drop',
                    'data' => $this->generateActivityLogData($item)
                ]
            );

            if ($item->image_path) {
                ImageUploaderHelper::deleteImage($item->image_path);
            }

            return $item;
        });
    }

    private function generateActivityLogData(CashierCashDrop $item)
    {
        $data = $item->getAttributes();
        $data['cashier_name'] = $item->cashier?->name;
        $data['source_account_name'] = $item->sourceFinanceAccount?->name;
        $data['target_account_name'] = $item->targetFinanceAccount?->name;
        $data['approver_name'] = $item->approver?->name;
        return $data;
    }
}
