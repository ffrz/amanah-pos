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
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnRefundService
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    public function findOrFail(int $id)
    {
        return PurchaseOrderPayment::with(['order', 'order.supplier'])->findOrFail($id);
    }

    public function addPayments(PurchaseOrder $order, array $payments): void
    {
        if ($order->remaining_debt <= 0) {
            throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($order, $payments) {
            $totalPaidAmount = 0;

            foreach ($payments as $inputPayment) {
                $amount = intval($inputPayment['amount']);

                if ($order->remaining_debt - $totalPaidAmount < $amount) {
                    throw new BusinessRuleViolationException('Jumlah pembayaran melebihi sisa tagihan.');
                }

                $totalPaidAmount += $amount;

                $payment = $this->createPaymentRecord($order, $inputPayment['id'] ?? null, $amount);

                $this->processFinancialRecords($payment);

                $this->updateSupplierDebtOnPayment($payment);
            }

            $order->applyPaymentUpdate($totalPaidAmount);

            $order->save();
        });
    }

    public function deletePayment(PurchaseOrderPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            /**
             * @var PurchaseOrder
             */
            $order = $payment->order;

            if ($order->status !== PurchaseOrder::Status_Closed) {
                throw new BusinessRuleViolationException('Tidak dapat menghapus pembayaran dari order yang belum selesai.');
            }

            $this->reverseFinancialRecords($payment);

            $this->reverseSupplierDebtOnPaymentDeletion($payment);

            $payment->delete();

            $order->applyPaymentUpdate(-$payment->amount);
            $order->save();
        });
    }

    // ---------------------------------------------
    //              HELPER METHODS
    // ---------------------------------------------

    /**
     * Membuat record pembayaran dan menentukan tipe/akun.
     */
    private function createPaymentRecord(PurchaseOrder $order, $accountId, int $amount): PurchaseOrderPayment
    {
        $payment = new PurchaseOrderPayment([
            'order_id' => $order->id,
            'finance_account_id' => $accountId,
            'supplier_id' => $order->supplier?->id,
            'type' => $accountId ? PurchaseOrderPayment::Type_Transfer : PurchaseOrderPayment::Type_Cash,
            'amount' => $amount,
        ]);

        $payment->save();

        return $payment;
    }

    /**
     * Mencatat transaksi keuangan (pengeluaran) dan mengurangi saldo akun.
     */
    private function processFinancialRecords(PurchaseOrderPayment $payment): void
    {
        $order = $payment->order;

        if ($payment->type === PurchaseOrderPayment::Type_Transfer) {
            FinanceTransaction::create([
                'account_id' => $payment->finance_account_id,
                'datetime' => now(),
                'type' => FinanceTransaction::Type_Expense,
                // Pembayaran adalah pengeluaran (negatif)
                'amount' => -$payment->amount,
                'ref_id' => $payment->id,
                'ref_type' => FinanceTransaction::RefType_PurchaseOrderPayment,
                'notes' => "Pembayaran transaksi #$order->formatted_id",
            ]);

            FinanceAccount::where('id', $payment->finance_account_id)
                ->decrement('balance', abs($payment->amount));
        }
    }

    /**
     * Memperbarui saldo utang supplier saat pembayaran diterima.
     */
    private function updateSupplierDebtOnPayment(PurchaseOrderPayment $payment): void
    {
        if (!$payment->order->supplier_id) return;

        // Pembayaran mengurangi utang, jadi nilainya positif
        $this->supplierService->addToBalance($payment->order->supplier, abs($payment->amount));
    }

    /**
     * Membalikkan transaksi keuangan (menghapus record transaksi dan mengembalikan saldo akun).
     */
    private function reverseFinancialRecords(PurchaseOrderPayment $payment): void
    {
        FinanceTransaction::deleteByRef($payment->id, FinanceTransaction::RefType_PurchaseOrderPayment);

        if ($payment->finance_account_id) {
            FinanceAccount::incrementBalance($payment->finance_account_id, abs($payment->amount));
        }
    }

    /**
     * Membalikkan utang supplier saat pembayaran dihapus.
     */
    private function reverseSupplierDebtOnPaymentDeletion(PurchaseOrderPayment $payment): void
    {
        if (!$payment->order->supplier_id) return;
        $this->supplierService->addToBalance($payment->order->supplier, -abs($payment->amount));
    }
}
