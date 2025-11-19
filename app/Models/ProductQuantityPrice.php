<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ProductQuantityPrice Model
 * Digunakan untuk menyimpan harga bertingkat (tiered/volume pricing)
 * berdasarkan kuantitas yang dibeli untuk unit produk tertentu.
 */
class ProductQuantityPrice extends BaseModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_quantity_prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_unit_id',
        'price_type',
        'min_quantity',
        'price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'min_quantity' => 'decimal:3',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke unit produk yang terkait.
     *
     * @return BelongsTo
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
