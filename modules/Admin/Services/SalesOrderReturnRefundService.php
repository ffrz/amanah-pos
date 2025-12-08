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
use App\Models\CustomerLedger;
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
    public function addRefund(SalesOrderReturn $soReturn, array $data): void
    {
        $this->ensureOrderIsProcessable($soReturn);

        if ($data['amount'] <= 0) {
            throw new BusinessRuleViolationException('Jumlah refund harus lebih dari nol.');
        }

        // if ($data['amount'] > ($soReturn->salesOrder->total_paid - $soReturn->total_refunded)) {
        //     throw new BusinessRuleViolationException('Jumlah refund melebihi batas yang diperbolehkan.');
        // }

        // if ($soReturn->remaining_refund <= 0) {
        //     throw new BusinessRuleViolationException('Pesanan ini sudah lunas.');
        // }

        DB::transaction(function () use ($soReturn, $data) {
            $amount = intval($data['amount']);
            $result = $this->processAndValidatePaymentMethod($soReturn, $data['id'], $amount);
            $refund = $this->createRefundRecord($soReturn, $result);
            $this->processFinancialRecords($refund);
            $soReturn->updateBalanceAndStatus();
            $soReturn->save();

            if ($soReturn->sales_order_id) {
                /**
                 * @var SalesOrder
                 */
                $salesOrder = $soReturn->salesOrder;
                if ($salesOrder) {
                    $salesOrder->updateTotals();
                    $salesOrder->save();
                }

                $customer = $salesOrder->customer;
                if ($customer) {
                    app(CustomerLedgerService::class)->save([
                        'customer_id' => $soReturn->customer_id,
                        'datetime'    => now(),
                        'type'        => CustomerLedger::Type_Refund,
                        'amount'      => abs($amount),
                        'notes'       => 'Refund tagihan transaksi penjualan #' . $soReturn->code . ' refund #' . $refund->code,
                        'ref_type'    => CustomerLedger::RefType_SalesOrderRefund,
                        'ref_id'      => $refund->id,
                    ]);
                    // $this->customerService->addToBalance($customer, -abs($refund->amount));
                }
            }

            $soReturn->updateBalanceAndStatus();
            $soReturn->save();
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

            app(CustomerLedgerService::class)->deleteByRef(
                CustomerLedger::RefType_SalesOrderRefund,
                $refund->id
            );
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
        $amount = abs($refund->amount);
        if ($refund->type === SalesOrderPayment::Type_Transfer || $refund->type === SalesOrderPayment::Type_Cash) {
            if ($refund->finance_account_id) {
                // membuat rekaman transaksi keuangan
                FinanceTransaction::create([
                    'account_id' => $refund->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Expense,
                    'amount' => -$amount,
                    'ref_id' => $refund->id,
                    'ref_type' => FinanceTransaction::RefType_SalesOrderReturnRefund,
                    'notes' => "Pembayaran transaksi #{$refund->return->code}",
                ]);

                // mengurangi saldo akun keuangan
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->decrement('balance', $amount);
            }
        } else if ($refund->type === SalesOrderPayment::Type_Wallet) {
            // Membuat rekaman transaksi wallet
            CustomerWalletTransaction::create([
                'customer_id' => $refund->return->customer_id,
                'datetime' => now(),
                'type' => CustomerWalletTransaction::Type_SalesOrderPayment,
                'amount' => $amount,
                'ref_type' => CustomerWalletTransaction::RefType_SalesOrderReturnRefund,
                'ref_id' => $refund->id,
                'notes' => "Pengembalian dana retur #{$refund->return->code}",
            ]);

            // tambah saldo wallet pelanggan
            Customer::where('id', $refund->return->customer_id)
                ->increment('wallet_balance', $amount);
        }
    }

    /**
     * Membalikkan transaksi keuangan dan mengembalikan saldo.
     */
    private function reverseFinancialRecords(SalesOrderPayment $payment): void
    {
        $amount = abs($payment->amount);
        if ($payment->type === SalesOrderPayment::Type_Transfer || $payment->type === SalesOrderPayment::Type_Cash) {
            if ($payment->finance_account_id) {
                // Hapus Transaksi Keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_SalesOrderReturnRefund)
                    ->delete();

                // Kembalikan Saldo Akun Keuangan (membalikkan increment saat bayar)
                FinanceAccount::where('id', $payment->finance_account_id)
                    ->increment('balance', $amount);
            }
        } else if ($payment->type === SalesOrderPayment::Type_Wallet) {
            // Batalkan transaksi dompet pelanggan
            CustomerWalletTransaction::where('ref_id', $payment->id)
                ->where('ref_type', CustomerWalletTransaction::RefType_SalesOrderReturnRefund)
                ->delete();

            // Kurangi saldo wallet
            Customer::where('id', $payment->return->customer_id)
                ->decrement('wallet_balance', $amount);
        }
    }
}
