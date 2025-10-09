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

class PurchaseOrderPaymentService
{
    public function __construct() {}

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
                if (!isset($inputPayment['id'])) {
                    throw new BusinessRuleViolationException('Invalid input payment format!');
                }

                $amount = intval($inputPayment['amount']);

                // Pastikan jumlah pembayaran tidak melebihi sisa tagihan
                if ($order->remaining_debt - $totalPaidAmount < $amount) {
                    throw new BusinessRuleViolationException('Jumlah pembayaran melebihi sisa tagihan.');
                }

                $totalPaidAmount += $amount;

                $accountId = null;
                $type = null;

                if (intval($inputPayment['id'])) {
                    $type = PurchaseOrderPayment::Type_Transfer;
                    $accountId = (int)$inputPayment['id'];
                }

                $payment = new PurchaseOrderPayment([
                    'order_id' => $order->id,
                    'finance_account_id' => $accountId,
                    'supplier_id' => $order->supplier?->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
                $payment->save();

                if ($type === PurchaseOrderPayment::Type_Transfer || $type === PurchaseOrderPayment::Type_Cash) {
                    FinanceTransaction::create([
                        'account_id' => $accountId,
                        'datetime' => now(),
                        'type' => FinanceTransaction::Type_Expense,
                        'amount' => -$amount,
                        'ref_id' => $payment->id,
                        'ref_type' => FinanceTransaction::RefType_PurchaseOrderPayment,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    FinanceAccount::where('id', $accountId)
                        ->decrement('balance', abs($amount));
                }
            }

            $order->applyPaymentUpdate($totalPaidAmount);
            $order->save();
        });
    }

    public function deletePayment(PurchaseOrderPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $order = $payment->order;

            if ($order->status !== PurchaseOrder::Status_Closed) {
                throw new BusinessRuleViolationException('Tidak dapat menghapus pembayaran dari order yang belum selesai.');
            }

            if ($payment->type === PurchaseOrderPayment::Type_Transfer || $payment->type === PurchaseOrderPayment::Type_Cash) {
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_PurchaseOrderPayment)
                    ->delete();

                if ($payment->finance_account_id) {
                    FinanceAccount::where('id', $payment->finance_account_id)
                        ->increment('balance', abs($payment->amount));
                }
            }

            $payment->delete();

            $order->applyPaymentUpdate(-$payment->amount);
        });
    }
}
