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
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\SalesOrderReturn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesOrderReturnRefundService
{
    public function __construct(
        protected CustomerService $customerService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function findOrFail(int $id): SalesOrderPayment
    {
        return SalesOrderPayment::with(['return', 'return.customer'])->findOrFail($id);
    }

    // OK
    public function addRefund(SalesOrderReturn $order, array $data): void
    {
        $this->ensureOrderIsProcessable($order);

        if ($data['amount'] <= 0) {
            throw new BusinessRuleViolationException('Jumlah refund harus lebih dari nol.');
        }

        // if ($data['amount'] > ($order->salesOrder->total_paid - $order->total_refunded)) {
        //     throw new BusinessRuleViolationException('Jumlah refund melebihi batas yang diperbolehkan.');
        // }

        // if ($order->remaining_refund <= 0) {
        //     throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        // }

        DB::transaction(function () use ($order, $data) {
            $amount = intval($data['amount']);
            $result = $this->processAndValidatePaymentMethod($order, $data['id'], $amount);
            $refund = $this->createRefundRecord($order, $result);
            $this->processFinancialRecords($refund);
            $order->updateBalanceAndStatus();
            $order->save();

            if ($order->sales_order_id) {
                $salesOrder = $order->salesOrder;
                if ($salesOrder) {
                    $salesOrder->updateTotals();
                    $salesOrder->save();
                }

                // dd($order->grand_total, $salesOrder->balance);

                $customer = $salesOrder->customer;
                if ($customer && $amount != 0) {
                    $this->customerService->addToBalance($customer, -$amount);
                }
            }
            // dd($order->remaining_refund);
        });
    }

    private function processAndValidatePaymentMethod($order, $type_or_id, $amount): array
    {
        $accountId = null;
        $type = null;
        if ($type_or_id === 'cash') {
            $type = SalesOrderPayment::Type_Cash;
            $session = $this->cashierSessionService->getActiveSession();
            if (!$session) {
                throw new BusinessRuleViolationException("Anda belum memulai sesi kasir.");
            }
            $accountId = $session->cashierTerminal->financeAccount->id;
        } else if ($type_or_id === 'wallet') {
            $type = SalesOrderPayment::Type_Wallet;
        } else if (intval($type_or_id)) {
            $type = SalesOrderPayment::Type_Transfer;
            $accountId = (int)$type_or_id;
        }

        return [
            'finance_account_id' => $accountId,
            'payment_type' => $type,
            'amount' => $amount,
        ];
    }

    public function deleteRefunds(SalesOrderReturn $orderReturn): void
    {
        $this->ensureOrderIsProcessable($orderReturn);

        DB::transaction(function () use ($orderReturn) {
            $orderReturn->loadMissing('payments');
            $this->deleteRefundsImpl($orderReturn->refunds);
        });
    }

    public function deleteRefund(SalesOrderPayment $refund): void
    {
        /**
         * @var SalesOrderReturn
         */
        $returnOrder = $refund->return;
        $this->ensureOrderIsProcessable($returnOrder);

        DB::transaction(function () use ($returnOrder, $refund) {
            $amount = $this->deleteRefundsImpl([$refund]);

            if ($returnOrder->salesOrder) {
                $salesOrder = $returnOrder->salesOrder;
                if ($salesOrder) {
                    $salesOrder->updateTotals();
                    $salesOrder->save();
                }

                $customer = $salesOrder->customer;
                if ($customer && $amount != 0) {
                    $this->customerService->addToBalance($customer, -$amount);
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
    private function ensureOrderIsProcessable(SalesOrderReturn $order)
    {
        if ($order->status !== SalesOrderReturn::Status_Closed) {
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

    /**
     * Membuat record pembayaran.
     */
    private function createRefundRecord(SalesOrderReturn $return, array $data): SalesOrderPayment
    {
        $refund = new SalesOrderPayment([
            'order_id' => $return->sales_order_id ?? null,
            'return_id' => $return->id,
            'finance_account_id' => $data['finance_account_id'],
            'customer_id' => $return->customer?->id,
            'type' => $data['payment_type'],
            'amount' => -$data['amount'], // REKAMAN REFUND SELALU NEGATIF!!!
            'notes' => $data['notes'] ?? '',
        ]);
        $refund->save();
        return $refund;
    }

    /**
     * Mencatat transaksi keuangan dan memperbarui saldo keuangan, wallet dan utang piutang.
     */
    private function processFinancialRecords(SalesOrderPayment $refund): void
    {
        if ($refund->type === SalesOrderPayment::Type_Transfer || $refund->type === SalesOrderPayment::Type_Cash) {
            if ($refund->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $refund->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Expense,
                    'amount' => -$refund->amount,
                    'ref_id' => $refund->id,
                    'ref_type' => FinanceTransaction::RefType_SalesOrderReturnRefund,
                    'notes' => "Pembayaran transaksi #{$refund->return->code}",
                ]);

                // mengurangi saldo akun keuangan
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->decrement('balance', $refund->amount);
            }
        } else if ($refund->type === SalesOrderPayment::Type_Wallet) {
            // Membuat rekaman transaksi wallet
            CustomerWalletTransaction::create([
                'customer_id' => $refund->salesOrderReturn->customer_id,
                'datetime' => now(),
                'type' => CustomerWalletTransaction::Type_SalesOrderPayment,
                'amount' => $refund->amount,
                'ref_type' => CustomerWalletTransaction::RefType_SalesOrderReturnRefund,
                'ref_id' => $refund->id,
                'notes' => "Pengembalian dana retur #{$refund->salesOrderReturn->code}",
            ]);

            // tambah saldo wallet pelanggan
            Customer::where('id', $refund->salesOrderReturn->customer_id)
                ->increment('wallet_balance', $refund->amount);
        }
    }

    /**
     * Membalikkan transaksi keuangan dan mengembalikan saldo.
     */
    private function reverseFinancialRecords(SalesOrderPayment $payment): void
    {
        if ($payment->type === SalesOrderPayment::Type_Transfer || $payment->type === SalesOrderPayment::Type_Cash) {
            if ($payment->finance_account_id) {
                // Hapus Transaksi Keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_SalesOrderReturnRefund)
                    ->delete();

                // Kembalikan Saldo Akun Keuangan (membalikkan increment saat bayar)
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->increment('balance', $payment->amount);
            }
        } else if ($payment->type === SalesOrderPayment::Type_Wallet) {
            // Batalkan transaksi dompet pelanggan
            CustomerWalletTransaction::where('ref_id', $payment->id)
                ->where('ref_type', CustomerWalletTransaction::RefType_SalesOrderReturnRefund)
                ->delete();

            // Tambah kembali saldo wallet pelanggan (membalikkan decrement saat bayar)
            Customer::where('id', $payment->salesOrderReturn->customer_id)
                ->decrement('wallet_balance', $payment->amount);
        }
    }
}
