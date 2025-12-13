<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ProductUnit Model
 * Menyimpan detail unit kemasan, faktor konversi, barcode, dan harga default.
 */
class ProductUnit extends BaseModel
{
    use HasFactory, SoftDeletes; // Asumsi model ini menggunakan SoftDeletes.

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_units';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'name',
        'barcode', // Kolom Barcode dari migrasi
        'conversion_factor',
        'is_base_unit',

        // --- Kolom Harga Default Unit dari migrasi ---
        'cost',
        'price_1',
        'price_2',
        'price_3',
        'price_1_markup',
        'price_2_markup',
        'price_3_markup',
        'price_1_option',
        'price_2_option',
        'price_3_option',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'conversion_factor' => 'float',
            'is_base_unit' => 'boolean',

            // --- Cast Harga Default Unit ---
            'cost' => 'decimal:2',
            'price_1' => 'decimal:2',
            'price_2' => 'decimal:2',
            'price_3' => 'decimal:2',
            'price_1_markup' => 'decimal:2',
            'price_2_markup' => 'decimal:2',
            'price_3_markup' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Product.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        // Model Product harus diimpor atau berada di namespace yang sama.
        return $this->belongsTo(Product::class);
    }

    /**
     * Relasi ke harga bertingkat berdasarkan kuantitas untuk unit ini.
     *
     * @return HasMany
     */
    public function quantityPrices(): HasMany
    {
        return $this->hasMany(ProductQuantityPrice::class);
    }

    // --- Accessors / Helpers ---

    /**
     * Mendapatkan faktor konversi dalam format yang mudah dibaca.
     *
     * @return string
     */
    public function getConversionFactorFormattedAttribute(): string
    {
        return number_format($this->conversion_factor, 4);
    }

    public function getSellingPrice(?string $priceType): float
    {
        $priceType = $priceType ?: 'price_1';

        // Cek Harga Grosir Unit
        if ($priceType === 'price_3') {
            $p3 = (float) $this->price_3;
            if ($p3 > 0) return $p3;
            $priceType = 'price_2';
        }

        // Cek Harga Partai Unit
        if ($priceType === 'price_2') {
            $p2 = (float) $this->price_2;
            if ($p2 > 0) return $p2;
        }

        // Fallback: Eceran Unit
        return (float) $this->price_1;
    }
}
