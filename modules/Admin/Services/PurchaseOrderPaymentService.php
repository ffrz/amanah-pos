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
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierWalletTransaction;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPaymentService
{
    public function __construct(
        protected SupplierService $supplierService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function findOrFail(int $id)
    {
        return PurchaseOrderPayment::with(['order', 'order.supplier'])->findOrFail($id);
    }

    public function addPayments(PurchaseOrder $order, array $payments, bool $updateSupplierBalance = true): void
    {
        if ($order->remaining_debt <= 0) {
            throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($order, $payments, $updateSupplierBalance) {
            if ($order->remaining_debt <= 0) {
                throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
            }

            $totalPaidAmount = 0;

            foreach ($payments as $inputPayment) {
                $amount = intval($inputPayment['amount']);

                if ($order->remaining_debt - $totalPaidAmount < $amount) {
                    throw new BusinessRuleViolationException('Jumlah pembayaran melebihi sisa tagihan.');
                }

                $totalPaidAmount += $amount;

                $payment = $this->createPaymentRecord($order, $inputPayment['id'] ?? null, $amount);

                $this->processFinancialRecords($payment);
            }

            if ($order->supplier_id && $updateSupplierBalance) {
                app(SupplierLedgerService::class)->save([
                    'supplier_id' => $order->supplier_id,
                    'datetime'    => now(),
                    'type'        => SupplierLedger::Type_Payment,
                    'amount'      => abs($amount),
                    'notes'       => 'Pembayaran tagihan transaksi penjualan #' . $order->code . ' payment #' . $payment->code,
                    'ref_type'    => SupplierLedger::RefType_PurchaseOrderPayment,
                    'ref_id'      => $payment->id,
                ]);
            }

            $order->updateTotals();
            $order->save();

            foreach ($order->returns as $return) {
                $return->updateBalanceAndStatus();
                $return->save();
            }
        });
    }

    public function deletePayment(PurchaseOrderPayment $payment, $updateSupplierBalance = true): void
    {
        DB::transaction(function () use ($payment, $updateSupplierBalance) {
            /**
             * @var PurchaseOrder
             */
            $order = $payment->order;

            if ($order->status !== PurchaseOrder::Status_Closed) {
                throw new BusinessRuleViolationException('Tidak dapat menghapus pembayaran dari order yang belum selesai.');
            }

            $this->reverseFinancialRecords($payment);

            $payment->delete();

            app(SupplierLedgerService::class)->deleteByRef(
                SupplierLedger::RefType_PurchaseOrderPayment,
                $payment->id
            );

            $order->updateTotals();
            $order->save();

            foreach ($order->returns as $return) {
                $return->updateBalanceAndStatus();
                $return->save();
            }
        });
    }

    // ---------------------------------------------
    //              HELPER METHODS
    // ---------------------------------------------

    /**
     * Membuat record pembayaran dan menentukan tipe/akun.
     */
    private function createPaymentRecord(PurchaseOrder $order, $type_or_id, int $amount): PurchaseOrderPayment
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
        } else if ($type_or_id === 'wallet') {
            $type = PurchaseOrderPayment::Type_Wallet;
            if ($order->supplier && $order->supplier->wallet_balance < $amount) {
                throw new BusinessRuleViolationException("Saldo wallet pemasok tidak mencukupi.");
            }
        } else if (intval($type_or_id)) {
            $type = PurchaseOrderPayment::Type_Transfer;
            $accountId = (int)$type_or_id;
        }

        $payment = new PurchaseOrderPayment([
            'order_id' => $order->id,
            'finance_account_id' => $accountId,
            'supplier_id' => $order->supplier?->id,
            'type' => $type,
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
        if ($payment->type === PurchaseOrderPayment::Type_Transfer || $payment->type === PurchaseOrderPayment::Type_Cash) {
            if ($payment->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $payment->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Expense,
                    'amount' => -$payment->amount,
                    'ref_id' => $payment->id,
                    'ref_type' => FinanceTransaction::RefType_PurchaseOrderPayment,
                    'notes' => "Pembayaran transaksi #{$payment->order->code}",
                ]);

                // mengurangi saldo akun keuangan
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->decrement('balance', $payment->amount);
            }
        } else if ($payment->type === PurchaseOrderPayment::Type_Wallet) {
            // Membuat rekaman transaksi wallet
            SupplierWalletTransaction::create([
                'supplier_id' => $payment->order->supplier_id,
                'datetime' => now(),
                'type' => SupplierWalletTransaction::Type_PurchaseOrderPayment,
                'amount' => -$payment->amount,
                'ref_type' => SupplierWalletTransaction::RefType_PurchaseOrderPayment,
                'ref_id' => $payment->id,
                'notes' => "Pembayaran transaksi #{$payment->order->code}",
            ]);

            // kurangi saldo wallet supplier
            Supplier::where('id', $payment->order->supplier_id)
                ->decrement('wallet_balance', $payment->amount);
        }
    }

    /**
     * Membalikkan transaksi keuangan (menghapus record transaksi dan mengembalikan saldo akun).
     */
    private function reverseFinancialRecords(PurchaseOrderPayment $payment): void
    {
        if ($payment->type === PurchaseOrderPayment::Type_Transfer || $payment->type === PurchaseOrderPayment::Type_Cash) {
            if ($payment->finance_account_id) {
                // Hapus Transaksi Keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_PurchaseOrderPayment)
                    ->delete();

                // Tambahkan saldo Akun Keuangan, saat baya kita kurangi, sekarang kita tambahkan
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->increment('balance', $payment->amount);
            }
        } else if ($payment->type === PurchaseOrderPayment::Type_Wallet) {
            // Hapus transaksi wallet
            SupplierWalletTransaction::where('ref_id', $payment->id)
                ->where('ref_type', SupplierWalletTransaction::RefType_PurchaseOrderPayment)
                ->delete();

            // Kembalikan saldo wallet
            Supplier::where('id', $payment->order->supplier_id)
                ->increment('wallet_balance', $payment->amount);
        }
    }
}
