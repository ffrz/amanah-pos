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

use App\Exceptions\BusinessRuleViolationException;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\PurchaseOrderRefund;
use App\Models\PurchaseOrderReturn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnRefundService
{
    public function __construct(
        protected SupplierService $supplierService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function findOrFail(int $id): PurchaseOrderRefund
    {
        return PurchaseOrderRefund::with(['purchaseOrderReturn', 'purchaseOrderReturn.Supplier'])->findOrFail($id);
    }

    public function addRefund(PurchaseOrderReturn $order, array $data): void
    {
        $this->ensureOrderIsProcessable($order);

        if ($data['amount'] <= 0) {
            throw new BusinessRuleViolationException('Jumlah refund harus lebih dari nol.');
        }

        if ($data['amount'] > ($order->purchaseOrder->total_paid - $order->total_refunded)) {
            throw new BusinessRuleViolationException('Jumlah refund melebihi batas yang diperbolehkan.');
        }

        if ($order->remaining_refund <= 0) {
            throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($order, $data) {
            $amount = intval($data['amount']);
            $result = $this->processAndValidatePaymentMethod($order, $data['id'], $amount);
            $refund = $this->createRefundRecord($order, $result);
            $this->processFinancialRecords($refund);
            $order->updateTotalRefunded($amount);
            $order->save();
        });
    }

    private function processAndValidatePaymentMethod($order, $type_or_id, $amount): array
    {
        $accountId = null;
        $type = null;
        if ($type_or_id === 'cash') {
            $type = PurchaseOrderRefund::Type_Cash;
            $session = $this->cashierSessionService->getActiveSession();
            if (!$session) {
                throw new BusinessRuleViolationException("Anda belum memulai sesi kasir.");
            }
            $accountId = $session->cashierTerminal->financeAccount->id;
        } else if (intval($type_or_id)) {
            $type = PurchaseOrderRefund::Type_Transfer;
            $accountId = (int)$type_or_id;
        }

        return [
            'finance_account_id' => $accountId,
            'payment_type' => $type,
            'amount' => $amount,
        ];
    }

    public function deleteRefunds(PurchaseOrderReturn $orderReturn): void
    {
        $this->ensureOrderIsProcessable($orderReturn);

        DB::transaction(function () use ($orderReturn) {
            $orderReturn->loadMissing('refunds');
            $total_amount = $this->deleteRefundsImpl($orderReturn->refunds);
            $orderReturn->updateTotalRefunded($total_amount);
            $orderReturn->save();
        });
    }

    public function deleteRefund(PurchaseOrderRefund $refund): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $returnOrder = $refund->purchaseOrderReturn;
        $this->ensureOrderIsProcessable($returnOrder);

        DB::transaction(function () use ($returnOrder, $refund) {
            $total_amount = $this->deleteRefundsImpl([$refund]);
            $returnOrder->updateTotalRefunded(-$total_amount);
            $returnOrder->save();
        });
    }

    // ---------------------------------------------
    //              HELPER METHODS
    // ---------------------------------------------

    /**
     * Memastikan order dapat diproses. Penghapusan pembayaran hanya boleh dilakukan
     * pada order yang sudah ditutup/selesai (Status_Closed).
     */
    private function ensureOrderIsProcessable(PurchaseOrderReturn $order)
    {
        if ($order->status !== PurchaseOrderReturn::Status_Closed) {
            throw new BusinessRuleViolationException('Tidak dapat memproses refund pembayaran.');
        }
    }

    /**
     * Logika inti untuk membalikkan transaksi dan menghapus record pembayaran.
     * Digunakan oleh deletePayment dan deletePayments.
     */
    private function deleteRefundsImpl(array|Collection $refunds)
    {
        $total_amount = 0;
        foreach ($refunds as $refund) {
            $this->reverseFinancialRecords($refund);
            $refund->delete();
            $total_amount += $refund->amount;
        }
        return $total_amount;
    }

    // OK
    private function createRefundRecord(PurchaseOrderReturn $order, array $data): PurchaseOrderRefund
    {
        $refund = new PurchaseOrderRefund([
            'purchase_order_return_id' => $order->id,
            'finance_account_id' => $data['finance_account_id'],
            'supplier_id' => $order->supplier?->id,
            'type' => $data['payment_type'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? '',
        ]);
        $refund->save();
        return $refund;
    }

    // OK
    private function processFinancialRecords(PurchaseOrderRefund $refund): void
    {
        if ($refund->type === PurchaseOrderRefund::Type_Transfer || $refund->type === PurchaseOrderRefund::Type_Cash) {
            if ($refund->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $refund->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Expense,
                    'amount' => $refund->amount,
                    'ref_id' => $refund->id,
                    'ref_type' => FinanceTransaction::RefType_PurchaseOrderReturnRefund,
                    'notes' => "Pembayaran transaksi #{$refund->purchaseOrderReturn->code}",
                ]);

                // menambah saldo akun keuangan
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->increment('balance', $refund->amount);
            }
        }
    }

    // OK
    private function reverseFinancialRecords(PurchaseOrderRefund $payment): void
    {
        if ($payment->type === PurchaseOrderRefund::Type_Transfer || $payment->type === PurchaseOrderRefund::Type_Cash) {
            if ($payment->finance_account_id) {
                // Hapus Transaksi Keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_PurchaseOrderReturnRefund)
                    ->delete();

                // Kembalikan Saldo Akun Keuangan (membalikkan decrement saat bayar)
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->decrement('balance', $payment->amount);
            }
        }
    }
}
