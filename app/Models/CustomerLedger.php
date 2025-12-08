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

use App\Models\Traits\HasTransactionCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CustomerLedger extends BaseModel
{
    use HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'CLTX'; // Customer Ledger Transaction

    protected $fillable = [
        'customer_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',             // invoice, payment, return, adjustment
        'amount',           // (+) Menambah Piutang, (-) Mengurangi Piutang
        'running_balance',  // Saldo setelah transaksi ini
        'image_path',
        'ref_type',         // Polymorphic Reference Type
        'ref_id',           // Polymorphic Reference ID
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
    const Type_Invoice        = 'invoice';         // (-) Tagihan Baru
    const Type_Refund         = 'refund';          // (-) Uang dikembalikan ke customer
    const Type_OpeningBalance = 'opening_balance'; // (-) Saldo Awal Utang

    // Grup Penambah Saldo (Melunasi Utang / Arah ke Plus)
    const Type_Payment        = 'payment';         // (+) Pembayaran Masuk
    const Type_CreditNote     = 'credit_note';     // (+) Retur Barang / Batal Utang

    // Netral / Manual
    const Type_Adjustment     = 'adjustment';      // (+/-) Penyesuaian Manual

    const Types = [
        self::Type_Invoice        => 'Tagihan Penjualan',
        self::Type_Refund         => 'Pengembalian Dana',
        self::Type_OpeningBalance => 'Saldo Awal Piutang',
        self::Type_Payment        => 'Pembayaran Masuk',
        self::Type_CreditNote     => 'Retur Penjualan',
        self::Type_Adjustment     => 'Penyesuaian',
    ];

    /**
     * Reference Types (Model Sumber).
     * Pastikan string ini sesuai dengan Morph Map di AppServiceProvider jika ada,
     * atau nama full class jika tidak pakai morph map.
     */
    const RefType_SalesOrder = 'sales_order';
    const RefType_SalesOrderPayment = 'sales_order_payment';
    const RefType_SalesOrderReturn = 'sales_order_return';
    const RefType_SalesOrderRefund = 'sales_order_refund';

    const RefTypes = [
        self::RefType_SalesOrder => 'Order Penjualan',
        self::RefType_SalesOrderPayment => 'Pembayaran Penjualan',
        self::RefType_SalesOrderReturn => 'Retur Penjualan',
        self::RefType_SalesOrderRefund => 'Refund Penjualan',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
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
            self::RefType_SalesOrder => SalesOrder::class,
            self::RefType_SalesOrderPayment => SalesOrderPayment::class,
            self::RefType_SalesOrderReturn  => SalesOrderReturn::class,
            self::RefType_SalesOrderRefund  => SalesOrderPayment::class,
        ]);
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    /**
     * Relasi ke Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi Polimorfik ke sumber transaksi (SalesOrder, Payment, dll).
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
            self::Type_Invoice,
            self::Type_Refund,
            self::Type_OpeningBalance => -1,

            // GRUP PENAMBAH SALDO (Arah ke Positif/Lunas)
            self::Type_Payment,
            self::Type_CreditNote => 1,

            // Adjustment dinamis
            default => 0,
        };
    }

    public function getMultiplierAttribute(): int
    {
        // Panggil fungsi statis di atas, lempar $this->type ke sana
        return self::getMultiplier($this->type);
    }

    public static function snapshotFromCustomer(Customer $c, float $balance): array
    {
        return [
            'customer_id'        => $c->id,
            'type'               => self::Type_OpeningBalance,
            'amount'             => $balance,
            'running_balance'    => $balance,
            'notes'              => 'Saldo awal setelah reset',
        ];
    }

    /**
     * Generate snapshot data from Customer master data.
     */
    public static function generateOpeningSnapshot()
    {
        $dummy = new static;
        $startSequence = 1;
        $now = Carbon::now();
        $auth_id = Auth::id();

        Customer::where('balance', '!=', 0)->chunk(500, function ($customers) use ($dummy, $now, $auth_id, &$startSequence) {
            $rows = [];

            foreach ($customers as $customer) {
                $data = self::snapshotFromCustomer($customer, $customer->balance);
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
