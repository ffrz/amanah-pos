<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Product Model
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'product_name',
        'category_id',
        'supplier_id',
        'name',
        'barcode',
        'description',
        'active',
        'type',
        'cost',
        'price',
        'uom',
        'stock',
        'min_stock',
        'max_stock',
        'notes',
    ];

    /**
     * Product types.
     */
    const Type_Stocked = 'stocked';
    const Type_NonStocked = 'nonstocked';
    const Type_Service = 'service';
    const Type_RawMaterial = 'raw_material';
    const Type_Composite = 'composite';

    const Types = [
        self::Type_Stocked => 'Stok',
        self::Type_NonStocked => 'Non Stok',
        self::Type_Service => 'Servis',
        self::Type_RawMaterial => 'Bahan Baku',
        self::Type_Composite => 'Komposit',
    ];

    protected function casts(): array
    {
        return [
            'product_id'   => 'integer',
            'product_name' => 'string',
            'category_id'  => 'integer',
            'supplier_id'  => 'integer',
            'name'         => 'string',
            'barcode'      => 'string',
            'description'  => 'string',
            'active'       => 'boolean',
            'type'         => 'string',
            'cost'         => 'decimal',
            'price'        => 'decimal',
            'uom'          => 'string',
            'stock'        => 'decimal',
            'min_stock'    => 'decimal',
            'max_stock'    => 'decimal',
            'notes'        => 'string',
        ];
    }


    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the supplier for the product.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the author of the product.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    /**
     * Get the updater of the product.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    /**
     * Get the number of active products.
     */
    public static function activeProductCount()
    {
        return DB::select(
            'select count(0) as count from products where active = 1'
        )[0]->count;
    }
}
