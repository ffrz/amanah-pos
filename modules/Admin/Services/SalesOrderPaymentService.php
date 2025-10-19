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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesOrderPaymentService
{
    public function __construct(
        protected CustomerService $customerService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function findOrFail(int $id): SalesOrderPayment
    {
        return SalesOrderPayment::with(['order', 'order.customer'])->findOrFail($id);
    }

    public function addPayments(SalesOrder $order, array $payments): void
    {
        $this->ensureOrderIsProcessable($order);

        if ($order->remaining_debt <= 0) {
            throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($order, $payments) {
            $totalPaidAmount = 0;

            foreach ($payments as $inputPayment) {
                $amount = intval($inputPayment['amount']);
                $result = $this->processAndValidatePaymentMethod($order, $inputPayment['id'], $amount);
                $payment = $this->createPaymentRecord($order, $result);
                $this->processFinancialRecords($payment);
                $totalPaidAmount += $amount;
            }

            if ($totalPaidAmount > $order->remaining_debt) {
                throw new BusinessRuleViolationException('Jumlah pembayaran melebihi sisa tagihan.');
            }

            $order->updateTotalPaid($order->total_paid + $totalPaidAmount);
            $order->save();
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
            if ($order->customer && $order->customer->wallet_balance < $amount) {
                throw new BusinessRuleViolationException("Saldo wallet pelanggan tidak mencukupi.");
            }
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

    public function deletePayments(SalesOrder $order): void
    {
        $this->ensureOrderIsProcessable($order);

        DB::transaction(function () use ($order) {
            // Memastikan relasi payments terload sebelum diteruskan ke impl
            $order->loadMissing('payments');
            $total_amount = $this->deletePaymentsImpl($order->payments);

            $order->updateTotalPaid($order->total_paid - $total_amount);
            $order->save();
        });
    }

    public function deletePayment(SalesOrderPayment $payment): void
    {
        /**
         * @var SalesOrder
         */
        $order = $payment->order;
        $this->ensureOrderIsProcessable($payment->order);

        if (!$order->customer_id) {
            throw new BusinessRuleViolationException("Tidak dapat mencatat utang tanpa ada pelanggan.");
        }

        DB::transaction(function () use ($order, $payment) {
            $this->deletePaymentsImpl([$payment]);
            $order->updateTotalPaid();
            $order->save();
        });
    }

    // ---------------------------------------------
    //              HELPER METHODS
    // ---------------------------------------------

    /**
     * Memastikan order dapat diproses. Penghapusan pembayaran hanya boleh dilakukan
     * pada order yang sudah ditutup/selesai (Status_Closed).
     */
    private function ensureOrderIsProcessable(SalesOrder $order)
    {
        if ($order->status !== SalesOrder::Status_Closed) {
            throw new BusinessRuleViolationException('Tidak dapat memproses pembayaran.');
        }
    }

    /**
     * Logika inti untuk membalikkan transaksi dan menghapus record pembayaran.
     * Digunakan oleh deletePayment dan deletePayments.
     */
    private function deletePaymentsImpl(array|Collection $payments)
    {
        $total_amount = 0;
        foreach ($payments as $payment) {
            $this->reverseFinancialRecords($payment);
            $payment->delete();
            $total_amount += $payment->amount;
        }
        return $total_amount;
    }

    /**
     * Membuat record pembayaran.
     */
    private function createPaymentRecord(SalesOrder $order, array $data): SalesOrderPayment
    {
        $payment = new SalesOrderPayment([
            'order_id' => $order->id,
            'finance_account_id' => $data['finance_account_id'],
            'customer_id' => $order->customer?->id,
            'type' => $data['payment_type'],
            'amount' => $data['amount'],
        ]);
        $payment->save();
        return $payment;
    }

    /**
     * Mencatat transaksi keuangan dan memperbarui saldo keuangan, wallet dan utang piutang.
     */
    private function processFinancialRecords(SalesOrderPayment $payment): void
    {
        if ($payment->type === SalesOrderPayment::Type_Transfer || $payment->type === SalesOrderPayment::Type_Cash) {
            if ($payment->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $payment->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Income,
                    'amount' => $payment->amount,
                    'ref_id' => $payment->id,
                    'ref_type' => FinanceTransaction::RefType_SalesOrderPayment,
                    'notes' => "Pembayaran transaksi #{$payment->order->code}",
                ]);

                // menambahkan saldo akun keuangan
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->increment('balance', $payment->amount);
            }
        } else if ($payment->type === SalesOrderPayment::Type_Wallet) {
            // Membuat rekaman transaksi wallet
            CustomerWalletTransaction::create([
                'customer_id' => $payment->order->customer_id,
                'datetime' => now(),
                'type' => CustomerWalletTransaction::Type_SalesOrderPayment,
                'amount' => -$payment->amount,
                'ref_type' => CustomerWalletTransaction::RefType_SalesOrderPayment,
                'ref_id' => $payment->id,
                'notes' => "Pembayaran transaksi #{$payment->order->code}",
            ]);

            // kurangi saldo wallet pelanggan
            Customer::where('id', $payment->order->customer_id)
                ->decrement('wallet_balance', $payment->amount);
        }

        if ($payment->order?->customer) {
            // Pembayaran mengurangi utang/piutang (balance) pelanggan
            Customer::where('id', $payment->order->customer->id)
                ->increment('balance', $payment->amount);
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
                    ->where('ref_type', FinanceTransaction::RefType_SalesOrderPayment)
                    ->delete();

                // Kurangi Saldo Akun Keuangan (membalikkan increment saat bayar)
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->decrement('balance', $payment->amount);
            }
        } else if ($payment->type === SalesOrderPayment::Type_Wallet) {
            // Batalkan transaksi dompet pelanggan
            CustomerWalletTransaction::where('ref_id', $payment->id)
                ->where('ref_type', CustomerWalletTransaction::RefType_SalesOrderPayment)
                ->delete();

            // Tambah kembali saldo wallet pelanggan (membalikkan decrement saat bayar)
            Customer::where('id', $payment->order->customer_id)
                ->increment('wallet_balance', $payment->amount);
        }

        if ($payment->order->customer_id) {
            // Tambah kembali utang/piutang pelanggan (membalikkan decrement saat bayar)
            Customer::where('id', $payment->order->customer_id)
                ->decrement('balance', $payment->amount);
        }
    }
}
