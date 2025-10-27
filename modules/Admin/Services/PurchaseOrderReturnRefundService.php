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
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseOrderReturn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnRefundService
{
    public function __construct(
        protected SupplierService $supplierService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function findOrFail(int $id): PurchaseOrderPayment
    {
        return PurchaseOrderPayment::with(['return', 'return.supplier'])->findOrFail($id);
    }

    public function addRefund(PurchaseOrderReturn $return, array $data): void
    {
        $this->ensureOrderIsProcessable($return);

        if ($data['amount'] <= 0) {
            throw new BusinessRuleViolationException('Jumlah refund harus lebih dari nol.');
        }

        if ($data['amount'] > ($return->purchaseOrder->total_paid - $return->total_refunded)) {
            throw new BusinessRuleViolationException('Jumlah refund melebihi batas yang diperbolehkan.');
        }

        if ($return->remaining_refund <= 0) {
            throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($return, $data) {
            $amount = intval($data['amount']);
            $result = $this->processAndValidatePaymentMethod($return, $data['id'], $amount);
            $refund = $this->createRefundRecord($return, $result);
            $this->processFinancialRecords($refund);
            $return->updateBalanceAndStatus();
            $return->save();

            if ($return->purchaseOrder) {
                $purchaseOrder = $return->purchaseOrder;
                if ($purchaseOrder) {
                    $purchaseOrder->updateTotals();
                    $purchaseOrder->save();
                }

                $supplier = $purchaseOrder->supplier;
                if ($supplier && $amount != 0) {
                    $this->supplierService->addToBalance($supplier, -$amount);
                }
            }

            $return->updateBalanceAndStatus();
            $return->save();
        });
    }

    private function processAndValidatePaymentMethod($return, $type_or_id, $amount): array
    {
        $accountId = null;
        $type = null;
        if ($type_or_id === 'cash') {
            $type = PurchaseOrderPayment::Type_Cash;
            $session = $this->cashierSessionService->getActiveSession();
            if (!$session) {
                throw new BusinessRuleViolationException("Anda belum memulai sesi kasir.");
            }
            $accountId = $session->cashierTerminal->financeAccount->id;
        } else if (intval($type_or_id)) {
            $type = PurchaseOrderPayment::Type_Transfer;
            $accountId = (int)$type_or_id;
        }

        return [
            'finance_account_id' => $accountId,
            'payment_type' => $type,
            'amount' => $amount,
        ];
    }

    // ini dipanggil kusus untuk delete retur
    public function deleteRefunds(PurchaseOrderReturn $orderReturn): void
    {
        $this->ensureOrderIsProcessable($orderReturn);

        DB::transaction(function () use ($orderReturn) {
            $orderReturn->loadMissing('payments');
            $this->deleteRefundsImpl($orderReturn->payments);
        });
    }

    // ini dipanggil kusus untuk delete refund satuan
    public function deleteRefund(PurchaseOrderPayment $refund): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $returnOrder = $refund->return;
        $this->ensureOrderIsProcessable($returnOrder);

        DB::transaction(function () use ($returnOrder, $refund) {
            $amount = $this->deleteRefundsImpl([$refund]);

            if ($returnOrder->purchaseOrder) {
                $purchaseOrder = $returnOrder->purchaseOrder;
                if ($purchaseOrder) {
                    $purchaseOrder->updateTotals();
                    $purchaseOrder->save();
                }

                $supplier = $purchaseOrder->supplier;
                if ($supplier && $amount != 0) {
                    $this->supplierService->addToBalance($supplier, $amount);
                }
            }

            $returnOrder->updateBalanceAndStatus();
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
    private function ensureOrderIsProcessable(PurchaseOrderReturn $return)
    {
        if ($return->status !== PurchaseOrderReturn::Status_Closed) {
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
    private function  createRefundRecord(PurchaseOrderReturn $return, array $data): PurchaseOrderPayment
    {
        $refund = new PurchaseOrderPayment([
            'order_id' => $return->purchase_order_id ?? null,
            'return_id' => $return->id,
            'finance_account_id' => $data['finance_account_id'],
            'supplier_id' => $return->supplier?->id,
            'type'   => $data['payment_type'],
            'amount' => $data['amount'],
            'notes'  => $data['notes'] ?? '',
        ]);
        $refund->save();
        return $refund;
    }

    // OK
    private function processFinancialRecords(PurchaseOrderPayment $refund): void
    {
        if ($refund->type === PurchaseOrderPayment::Type_Transfer || $refund->type === PurchaseOrderPayment::Type_Cash) {
            if ($refund->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $refund->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Income,
                    'amount' => $refund->amount,
                    'ref_id' => $refund->id,
                    'ref_type' => FinanceTransaction::RefType_PurchaseOrderReturnRefund,
                    'notes' => "Terima refund pembelian #{$refund->return->code}",
                ]);

                // menambah saldo akun keuangan
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->increment('balance', $refund->amount);
            }
        }
    }

    // OK
    private function reverseFinancialRecords(PurchaseOrderPayment $payment): void
    {
        if ($payment->type === PurchaseOrderPayment::Type_Transfer || $payment->type === PurchaseOrderPayment::Type_Cash) {
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
