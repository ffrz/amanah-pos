<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory;

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'parent_id');
    }
}
