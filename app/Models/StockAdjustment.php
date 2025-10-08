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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends BaseModel
{
    use HasFactory,
        HasDocumentVersions;

    protected $fillable = [
        'datetime',
        'status',
        'type',
        'total_cost',
        'total_price',
        'notes',
    ];

    // === Type ===
    public const Type_StockOpname       = 'stock_opname';
    public const Type_StockCorrection   = 'stock_correction';
    public const Type_Lost              = 'lost';
    public const Type_InternalUse       = 'internal_use';
    public const Type_Expired           = 'expired';

    public const Types = [
        self::Type_StockOpname      => 'Stok Opname',
        self::Type_StockCorrection  => 'Koreksi Stok',
        self::Type_Lost             => 'Hilang',
        self::Type_InternalUse      => 'Penggunaan Internal',
        self::Type_Expired          => 'Kedaluwasa',
    ];

    // === Status ===
    public const Status_Draft     = 'draft';
    public const Status_Closed    = 'closed';
    public const Status_Cancelled = 'cancelled';

    public const Statuses = [
        self::Status_Draft     => 'Draft',
        self::Status_Closed    => 'Selesai',
        self::Status_Cancelled => 'Dibatalkan',
    ];

    protected $appends = [
        'formatted_id',
        'type_label'
    ];

    protected function casts(): array
    {
        return [
            'datetime'     => 'datetime',
            'status'       => 'string',
            'type'         => 'string',
            'total_cost'   => 'decimal:2',
            'total_price'  => 'decimal:2',
            'notes'        => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type];
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'parent_id');
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('stock_adjustment_code_prefix', 'SA-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }
}
