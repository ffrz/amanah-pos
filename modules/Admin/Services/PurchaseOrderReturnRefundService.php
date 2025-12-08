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
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierWalletTransaction;
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

        // Validasi input
        $inputAmount = intval($data['amount']);

        if ($inputAmount <= 0) {
            throw new BusinessRuleViolationException('Jumlah refund harus lebih dari nol.');
        }

        // if ($inputAmount > ($return->purchaseOrder->total_paid - $return->total_refunded)) {
        //     dd($return->purchaseOrder->total_paid, $return->total_refunded, $inputAmount);
        //     throw new BusinessRuleViolationException('Jumlah refund melebihi batas yang diperbolehkan.');
        // }

        // if ($return->remaining_refund <= 0) {
        //     throw new BusinessRuleViolationException('Pesanan ini sudah lunas direfund.');
        // }

        DB::transaction(function () use ($return, $data, $inputAmount) {

            $result = $this->processAndValidatePaymentMethod($return, $data['id'] ?? 'cash', $inputAmount);

            // 1. Simpan Record Refund (Payment dengan type Income/Refund)
            $refund = $this->createRefundRecord($return, $result);

            // 2. Proses Keuangan (Tambah Kas / Tambah Wallet)
            $this->processFinancialRecords($refund);

            // 3. Update status Return Order
            $return->updateBalanceAndStatus();
            $return->save();

            // 4. Update status Purchase Order Induk
            if ($return->purchaseOrder) {
                $purchaseOrder = $return->purchaseOrder;
                $purchaseOrder->updateTotals();
                $purchaseOrder->save();

                if ($return->supplier_id) {
                    app(SupplierLedgerService::class)->save([
                        'supplier_id' => $return->supplier_id,
                        'datetime'    => now(),
                        'type'        => SupplierLedger::Type_Refund,
                        'amount'      => abs($inputAmount),
                        'notes'       => 'Refund tagihan transaksi pembelian #' . $return->code . ' refund #' . $refund->code,
                        'ref_type'    => SupplierLedger::RefType_PurchaseOrderRefund,
                        'ref_id'      => $refund->id,
                    ]);
                }
            }
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
        } else if ($type_or_id === 'wallet') {
            // Refund ke Wallet = Deposit Supplier Bertambah
            $type = PurchaseOrderPayment::Type_Wallet;
            // Tidak perlu cek saldo mencukupi, karena saldo supplier justru akan bertambah
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

            $orderReturn->updateBalanceAndStatus();
            $orderReturn->save();
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
            }

            $returnOrder->updateBalanceAndStatus();
            $returnOrder->save();
        });
    }

    // ---------------------------------------------
    //              HELPER METHODS
    // ---------------------------------------------

    private function ensureOrderIsProcessable(PurchaseOrderReturn $return)
    {
        // Refund biasanya hanya bisa dilakukan jika status return sudah disetujui/closed/processed
        // Sesuaikan dengan flow bisnis Anda.
        if ($return->status === PurchaseOrderReturn::Status_Draft) {
            throw new BusinessRuleViolationException('Return belum diproses, tidak bisa melakukan refund.');
        }
    }

    /**
     * Logika inti untuk membalikkan transaksi dan menghapus record pembayaran.
     * Digunakan oleh deleteRefund dan deleteRefunds.
     */
    private function deleteRefundsImpl(array|Collection $refunds)
    {
        $total_amount = 0;
        foreach ($refunds as $refund) {
            $this->reverseFinancialRecords($refund);
            $refund->delete();
            $total_amount += $refund->amount;

            app(SupplierLedgerService::class)->deleteByRef(
                SupplierLedger::RefType_PurchaseOrderRefund,
                $refund->id
            );
        }
        return $total_amount;
    }

    private function createRefundRecord(PurchaseOrderReturn $return, array $data): PurchaseOrderPayment
    {
        $refund = new PurchaseOrderPayment([
            'order_id' => $return->purchase_order_id ?? null,
            'return_id' => $return->id,
            'finance_account_id' => $data['finance_account_id'],
            'supplier_id' => $return->supplier?->id,
            'type'   => $data['payment_type'],
            'amount' => -$data['amount'],
            'notes'  => $data['notes'] ?? '',
        ]);
        $refund->save();
        return $refund;
    }

    private function processFinancialRecords(PurchaseOrderPayment $refund): void
    {
        $amount = abs($refund->amount);

        // CASE 1: Refund diterima via Kas/Transfer (Uang Masuk)
        if ($refund->type === PurchaseOrderPayment::Type_Transfer || $refund->type === PurchaseOrderPayment::Type_Cash) {
            if ($refund->finance_account_id) {
                // Catat Pemasukan
                FinanceTransaction::create([
                    'account_id' => $refund->finance_account_id,
                    'datetime' => now(),
                    'type' => FinanceTransaction::Type_Income, // Income karena terima uang
                    'amount' => $amount, // Positif
                    'ref_id' => $refund->id,
                    'ref_type' => FinanceTransaction::RefType_PurchaseOrderReturnRefund,
                    'notes' => "Terima refund retur #{$refund->return->code}",
                ]);

                // Tambah Saldo Akun
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->increment('balance', $amount);
            }
        }
        // CASE 2: Refund masuk ke Wallet Supplier (Deposit Bertambah)
        else if ($refund->type === PurchaseOrderPayment::Type_Wallet) {
            // Catat Transaksi Wallet
            SupplierWalletTransaction::create([
                'supplier_id' => $refund->supplier_id,
                'datetime' => now(),
                'type' => SupplierWalletTransaction::Type_PurchaseOrderReturnRefund,
                'amount' => $amount, // Positif (Deposit nambah)
                'ref_type' => SupplierWalletTransaction::RefType_PurchaseOrderReturnRefund,
                'ref_id' => $refund->id,
                'notes' => "Refund retur #{$refund->return->code} masuk deposit",
            ]);

            // Tambah Saldo Wallet Supplier
            Supplier::where('id', $refund->supplier_id)
                ->increment('wallet_balance', $amount);
        }
    }

    private function reverseFinancialRecords(PurchaseOrderPayment $refund): void
    {
        $amount = abs($refund->amount);

        // REVERSE CASE 1: Batalkan Refund Kas/Transfer
        if ($refund->type === PurchaseOrderPayment::Type_Transfer || $refund->type === PurchaseOrderPayment::Type_Cash) {
            if ($refund->finance_account_id) {
                // Hapus Transaksi Keuangan
                FinanceTransaction::where('ref_id', $refund->id)
                    ->where('ref_type', FinanceTransaction::RefType_PurchaseOrderReturnRefund)
                    ->delete();

                // Kurangi Saldo Akun Keuangan (Kembalikan ke sebelum terima refund)
                FinanceAccount::where('id', $refund->finance_account_id)
                    ->decrement('balance', $amount);
            }
        }
        // REVERSE CASE 2: Batalkan Refund Wallet
        else if ($refund->type === PurchaseOrderPayment::Type_Wallet) {
            // Hapus Transaksi Wallet
            SupplierWalletTransaction::where('ref_id', $refund->id)
                ->where('ref_type', SupplierWalletTransaction::RefType_PurchaseOrderReturnRefund)
                ->delete();

            // Kurangi Saldo Wallet Supplier (Kembalikan deposit yang sempat bertambah)
            Supplier::where('id', $refund->supplier_id)
                ->decrement('wallet_balance', $amount);
        }
    }
}
