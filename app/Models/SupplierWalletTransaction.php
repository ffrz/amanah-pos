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

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierWalletTransaction extends BaseModel
{
    use HasFactory,
        HasDocumentVersions,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'SWTX';

    protected $fillable = [
        'supplier_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',
        'amount',
        'image_path',
        'ref_type',
        'ref_id',
        'notes',
    ];

    protected $appends = [
        'type_label',

    ];

    /**
     * Transaction types.
     */
    const Type_OpeningBalance = 'opening_balance';
    const Type_Adjustment = 'adjustment';
    const Type_Refund = 'refund';
    const Type_Deposit = 'deposit';
    const Type_PurchaseOrderPayment = 'purchase_order_payment';
    const Type_PurchaseOrderReturnRefund = 'purchase_order_return_refund';
    const Type_Withdrawal = 'withdrawal';

    const Types = [
        self::Type_OpeningBalance   => 'Saldo Awal (Migrasi)',
        self::Type_Adjustment => 'Penyesuaian',
        self::Type_Deposit => 'Depoist',
        self::Type_Refund => 'Refund',
        self::Type_Withdrawal => 'Penarikan',
        self::Type_PurchaseOrderPayment => 'Pembelian',
        self::Type_PurchaseOrderReturnRefund => 'Refund Pembelian',
    ];

    const RefType_PurchaseOrderPayment = 'purchase_order_payment';
    const RefType_PurchaseOrderReturnRefund = 'purchase_order_return_refund';
    const RefType_SupplierWalletTransactionConfirmation = 'supplier_wallet_transaction_confirmation';

    const RefTypes = [
        self::RefType_PurchaseOrderPayment => 'Transaksi Pembayaran Penjualan',
        self::RefType_PurchaseOrderReturnRefund => ' Transaksi Refund Pembayaran Penjualan',
        self::RefType_SupplierWalletTransactionConfirmation => 'Konfirmasi Transaksi Wallet Pelanggan',
    ];

    protected function casts(): array
    {
        return [
            'supplier_id' => 'integer',
            'finance_account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'amount' => 'decimal:2',
            'ref_type' => 'string',
            'ref_id' => 'integer',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'image_path' => 'string'
        ];
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public static function snapshotFromSupplier(Supplier $s, float $balance): array
    {
        return [
            'supplier_id'        => $s->id,
            'finance_account_id' => null,

            'datetime'           => now(),
            'type'               => self::Type_OpeningBalance,
            'amount'             => $balance,

            'ref_type'           => null,
            'ref_id'             => null,

            'notes'              => 'Saldo awal wallet supplier setelah reset',

            'created_at'         => now(),
            'updated_at'         => now(),
        ];
    }

    /**
     * Generate snapshot data from Supplier master data.
     * Uses bulk insert with manual transaction code generation for high performance.
     */
    public static function generateOpeningSnapshot()
    {
        // 1. Dapatkan konfigurasi Prefix & Padding dari Model
        $dummy = new static;
        $prefix = method_exists($dummy, 'getTransactionPrefix')
            ? $dummy->getTransactionPrefix()
            : ($dummy->transactionPrefix ?? 'SWTX');

        $padSize = method_exists($dummy, 'getTransactionNumberPadSize')
            ? $dummy->getTransactionNumberPadSize()
            : 5;
        if ($padSize == 0) $padSize = 5; // Default padding jika 0

        $dateCode = now()->format('ymd');

        // Karena tabel baru di-truncate (resetTransaction), ID pasti mulai dari 1
        $startSequence = 1;

        // 2. Query Data Master & Bulk Insert per Chunk
        \App\Models\Supplier::where('wallet_balance', '!=', 0)->chunk(500, function ($suppliers) use ($prefix, $dateCode, $padSize, &$startSequence) {
            $rows = [];

            foreach ($suppliers as $supplier) {
                // Generate Code Manual: Prefix-ymd-Sequence (Increment di PHP)
                $code = $prefix . '-' . $dateCode . '-' . str_pad($startSequence++, $padSize, '0', STR_PAD_LEFT);

                // Ambil mapping data standar dari Model
                $data = self::snapshotFromSupplier($supplier, $supplier->wallet_balance);

                // Tambahkan kode transaksi unik ke array
                $data['code'] = $code;

                $rows[] = $data;
            }

            // Lakukan Insert Sekaligus untuk 500 baris
            if (!empty($rows)) {
                static::insert($rows);
            }
        });
    }
}
