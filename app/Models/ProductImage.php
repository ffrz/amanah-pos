<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductImage Model
 * Menyimpan metadata dan path untuk gambar yang terkait dengan suatu produk.
 */
class ProductImage extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'path',
        'caption',
        'is_featured',
        'sort_order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Relasi ke Produk.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor untuk mendapatkan URL publik gambar.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return url('products/' . $this->path);
    }
}
