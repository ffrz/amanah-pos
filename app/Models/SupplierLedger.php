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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SupplierLedger extends BaseModel
{
    use HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'SLTX'; // Supplier Ledger Transaction

    protected $fillable = [
        'supplier_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',             // bill, payment, return, adjustment
        'amount',           // (+) Menambah Utang Kita, (-) Mengurangi Utang Kita
        'running_balance',  // Saldo setelah transaksi ini
        'image_path',
        'ref_type',
        'ref_id',
        'notes',
    ];

    protected $appends = [
        'type_label',
    ];

    /**
     * DEFINISI JENIS TRANSAKSI
     * * Logika Saldo (Net Balance):
     * (-) Negatif : Beban/Utang (Invoice, Saldo Awal Utang)
     * (+) Positif : Bayar/Simpanan (Payment, Retur)
     */

    // Grup Pengurang Saldo (Menambah Utang / Arah ke Minus)
    const Type_Bill           = 'invoice';         // (-) Tagihan Baru
    const Type_Refund         = 'refund';          // (-) Uang dikembalikan ke customer
    const Type_OpeningBalance = 'opening_balance'; // (-) Saldo Awal Utang

    // Grup Penambah Saldo (Melunasi Utang / Arah ke Plus)
    const Type_Payment        = 'payment';         // (+) Pembayaran Masuk
    const Type_DebitNote      = 'debit_note';      // (+) Retur Barang / Batal Utang

    // Netral / Manual
    const Type_Adjustment     = 'adjustment';      // (+/-) Penyesuaian Manual

    const Types = [
        self::Type_Bill           => 'Tagihan Pembelian',   // Beda Istilah dengan Customer (Invoice vs Bill)
        self::Type_Refund         => 'Terima Refund Dana',  // Beda Arah Uang
        self::Type_OpeningBalance => 'Saldo Awal Utang',
        self::Type_Payment        => 'Pembayaran Keluar',   // Beda Arah Uang
        self::Type_DebitNote      => 'Retur Pembelian',     // Istilah Debit Note (Lawan dari Credit Note)
        self::Type_Adjustment     => 'Penyesuaian',
    ];

    /**
     * Reference Types.
     */
    const RefType_PurchaseOrder = 'purchase_order';
    const RefType_PurchaseOrderPayment = 'purchase_order_payment';
    const RefType_PurchaseOrderReturn = 'purchase_order_return';
    const RefType_PurchaseOrderRefund = 'purchase_order_refund';

    const RefTypes = [
        self::RefType_PurchaseOrder => 'Order Pembelian',
        self::RefType_PurchaseOrderPayment => 'Pembayaran Pembelian',
        self::RefType_PurchaseOrderReturn => 'Retur Pembelian',
        self::RefType_PurchaseOrderRefund => 'Refund Pembelian',
    ];

    protected function casts(): array
    {
        return [
            'supplier_id' => 'integer',
            'finance_account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
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

    protected static function booted()
    {
        parent::booted();

        Relation::morphMap([
            self::RefType_PurchaseOrder => PurchaseOrder::class,
            self::RefType_PurchaseOrderPayment => PurchaseOrderPayment::class,
            self::RefType_PurchaseOrderReturn  => PurchaseOrderReturn::class,
            self::RefType_PurchaseOrderRefund  => PurchaseOrderPayment::class,
        ]);
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    /**
     * Relasi ke Supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi Polimorfik ke sumber transaksi.
     */
    public function ref()
    {
        return $this->morphTo();
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public static function getMultiplier(string $type): int
    {
        return match ($type) {
            // GRUP PENGURANG SALDO (Arah ke Negatif/Utang)
            self::Type_Bill,
            self::Type_Refund,
            self::Type_OpeningBalance => -1,

            // GRUP PENAMBAH SALDO (Arah ke Positif/Lunas)
            self::Type_Payment,
            self::Type_DebitNote => 1,

            // Adjustment dinamis
            default => 0,
        };
    }

    public function getMultiplierAttribute(): int
    {
        // Panggil fungsi statis di atas, lempar $this->type ke sana
        return self::getMultiplier($this->type);
    }

    public static function snapshotFromSupplier(Supplier $s, float $balance): array
    {
        return [
            'supplier_id'       => $s->id,
            'finance_account_id' => null,
            'type'              => self::Type_OpeningBalance,
            'amount'            => $balance,
            'running_balance'   => $balance,
            'ref_type'          => null,
            'ref_id'            => null,
            'notes'             => 'Saldo awal setelah reset',
        ];
    }

    /**
     * Generate snapshot data from Supplier master data.
     */
    public static function generateOpeningSnapshot()
    {
        $dummy = new static;
        $startSequence = 1;
        $now = Carbon::now();
        $auth_id = Auth::id();

        Supplier::where('balance', '!=', 0)->chunk(500, function ($suppliers) use ($dummy, $now, $auth_id, &$startSequence) {
            $rows = [];

            foreach ($suppliers as $supplier) {
                $data = self::snapshotFromSupplier($supplier, $supplier->balance);
                $data['code'] = $dummy->generateTransactionCodeWithDateAndSequence($now, $startSequence++);
                $data['datetime'] = $now;
                $data['created_at'] = $now;
                $data['created_by'] = $auth_id;
                $rows[] = $data;
            }

            if (!empty($rows)) {
                static::insert($rows);
            }
        });
    }
}
