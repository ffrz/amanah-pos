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

use App\Helpers\ImageUploaderHelper;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionConfirmationService
{
    public function __construct(
        protected FinanceTransactionService $transactionService,
        protected CustomerWalletTransactionService $walletService,
        protected UserActivityLogService $activityLogService,
        protected DocumentVersionService $docVersionService,
    ) {}

    public function find($id): CustomerWalletTransactionConfirmation
    {
        return CustomerWalletTransactionConfirmation::with(['financeAccount', 'customer', 'updater'])
            ->findOrFail($id);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $q = CustomerWalletTransactionConfirmation::with([
            'customer' => function ($query) {
                $query->select('id', 'code', 'name');
            },
            'financeAccount' => function ($query) {
                $query->select('id', 'name', 'bank', 'holder', 'number');
            },
        ]);

        $filter = $options['filter'];

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


        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', $filter['customer_id']);
        }

        if (!empty($filter['finance_account_id']) && $filter['finance_account_id'] != 'all') {
            $q->where('finance_account_id', '=', $filter['finance_account_id']);
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function save(CustomerWalletTransactionConfirmation $item, $status): CustomerWalletTransactionConfirmation
    {
        return DB::transaction(function () use ($item, $status) {
            $item->status = $status;
            $item->save();

            if ($item->status === CustomerWalletTransactionConfirmation::Status_Approved) {
                $walletTransaction = $this->walletService->handleTransaction([
                    'customer_id' => $item->customer_id,
                    'finance_account_id' => $item->finance_account_id,
                    'datetime' => Carbon::now(),
                    'type' => CustomerWalletTransaction::Type_Deposit,
                    'ref_type' => CustomerWalletTransaction::RefType_CustomerWalletTransactionConfirmation,
                    'ref_id' => $item->id,
                    'amount' => $item->amount,
                    'notes' => 'Konfirmasi topup wallet otomatis #' . $item->code,
                ]);

                $this->transactionService->handleTransaction([
                    'datetime' => Carbon::now(),
                    'account_id' => $item->finance_account_id,
                    'amount' => $item->amount,
                    'type' => FinanceTransaction::Type_Income,
                    'notes' => 'Transaksi topup wallet customer ' . $item->customer->code . ' Ref: #' . $walletTransaction->code,
                    'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
                    'ref_id' => $item->id,
                ]);

                $this->activityLogService->log(
                    UserActivityLog::Category_CustomerWallet,
                    UserActivityLog::Name_CustomerWalletTopupConfirmation_Approve,
                    "Konfirmasi wallet $item->fomatted_id telah disetujui.",
                    [
                        'data' => $item->toArray(),
                        'formatter' => 'customer-wallet-transaction-confirmation',
                    ]
                );
            } else if ($status === CustomerWalletTransactionConfirmation::Status_Rejected) {
                $this->activityLogService->log(
                    UserActivityLog::Category_CustomerWallet,
                    UserActivityLog::Name_CustomerWalletTopupConfirmation_Reject,
                    "Konfirmasi wallet $item->fomatted_id telah ditolak.",
                    [
                        'data' => $item->toArray(),
                        'formatter' => 'customer-wallet-transaction-confirmation',
                    ]
                );
            } else {
                // aksi lainnya belum ada / belum ditangani
            }

            $this->docVersionService->createVersion($item);

            return $item;
        });
    }

    public function delete(CustomerWalletTransactionConfirmation $item): CustomerWalletTransactionConfirmation
    {
        return DB::transaction(function () use ($item) {
            // kembalikan saldo walet dan saldo akun kas jika sudah di approve
            if ($item->status === CustomerWalletTransactionConfirmation::Status_Approved) {
                $this->walletService->reverseTransaction(
                    $item->id,
                    CustomerWalletTransaction::RefType_CustomerWalletTransactionConfirmation
                );

                $this->transactionService->reverseTransaction(
                    $item->id,
                    FinanceTransaction::RefType_CustomerWalletTransaction
                );
            }

            $item->delete();

            // coba hapus foto
            ImageUploaderHelper::deleteImage($item->image_path);

            // log aktifitas
            $this->activityLogService->log(
                UserActivityLog::Category_CustomerWallet,
                UserActivityLog::Name_CustomerWalletTopupConfirmation_Delete,
                "Konfirmasi wallet $item->code telah dihapus.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'customer-wallet-transaction-confirmation',
                ]
            );

            $this->docVersionService->createDeletedVersion($item);

            return $item;
        });
    }
}
