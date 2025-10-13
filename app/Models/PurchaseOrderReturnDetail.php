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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReturnDetail extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_return_id', // Foreign key ke dokumen retur
        'purchase_order_detail_id', // Link ke detail order asli
        'product_id',
        'product_name',
        'product_barcode',
        'product_description',
        'product_uom',
        'quantity',
        'cost',
        'subtotal_cost',
        'reason', // Alasan retur (per item)
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_order_return_id' => 'integer',
            'purchase_order_detail_id' => 'integer',
            'product_id'                => 'integer',
            'product_name'                => 'string',
            'product_description'      => 'string',
            'product_uom'                => 'string',
            'product_barcode'          => 'string',
            'quantity'                    => 'decimal:3',
            'cost'                        => 'decimal:2',
            'subtotal_cost'            => 'decimal:2',
            'reason'                   => 'string',
            'notes'                    => 'string',
        ];
    }

    // === Accessors (Sesuai dengan pola Anda) ===

    protected function getQuantityAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getCostAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getSubtotalCostAttribute(string $value): float
    {
        return (float) $value;
    }

    // === Relasi ===

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReturn::class, 'purchase_order_return_id');
    }

    public function originalDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'purchase_order_detail_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
