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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Product Model
 */
class Product extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'category_id',
        'supplier_id',
        'name',
        'barcode',
        'description',
        'active',
        'price_editable',
        'type',
        'cost',
        'price',
        'price_2',
        'price_3',
        'uom',
        'stock',
        'min_stock',
        'max_stock',
        'notes',
    ];

    protected $appends = [
        'type_label',
        'formatted_id',
    ];

    /**
     * Product types.
     */
    const Type_Stocked = 'stocked';
    const Type_NonStocked = 'nonstocked';
    const Type_Service = 'service';
    const Type_RawMaterial = 'raw_material';
    const Type_Composite = 'composite';
    const Type_Consignment = 'consignment';

    /**
     * Product types mapping.
     * @var array
     */
    const Types = [
        self::Type_Stocked => 'Stok',
        self::Type_NonStocked => 'Non Stok',
        self::Type_Service => 'Servis',
        self::Type_RawMaterial => 'Bahan Baku',
        self::Type_Composite => 'Komposit',
        self::Type_Consignment => 'Konsinyasi',
    ];

    /**
     * The attributes that should be cast to native types.
     * @return array
     * This method defines the data types for each attribute in the Product model.
     * It ensures that when the model is retrieved from the database, the attributes are automatically cast
     * to the specified types, such as integer, string, boolean, decimal, and datetime.
     * This helps maintain data integrity and makes it easier to work with the model's attributes in
     * a type-safe manner.
     */
    protected function casts(): array
    {
        return [
            'product_id'  => 'integer',
            'category_id' => 'integer',
            'supplier_id' => 'integer',
            'name'        => 'string',
            'barcode'     => 'string',
            'description' => 'string',
            'active'      => 'boolean',
            'price_editable' => 'boolean',
            'type'       => 'string',
            'cost'       => 'decimal:2',
            'price'      => 'decimal:2',
            'price_2'    => 'decimal:2',
            'price_3'    => 'decimal:2',
            'uom'        => 'string',
            'stock'      => 'decimal:3',
            'min_stock'  => 'decimal:3',
            'max_stock'  => 'decimal:3',
            'notes'      => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('product_code_prefix', 'P-') . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    /**
     * Get the category for the product.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the supplier for the product.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the number of active products.
     * @return int
     * This method retrieves the count of products that are currently active (where active = 1).
     * It uses a raw SQL query to count the number of rows in the products table that
     * have the active status set to 1.
     * This is useful for quickly checking how many products are available for sale or use.
     */
    public static function activeProductCount()
    {
        return DB::select(
            'select count(0) as count from products where active = 1'
        )[0]->count;
    }

    /**
     * Check if the product is used in any transactions.
     * @return bool
     * This method checks if the product is referenced in stock movements, stock adjustments, purchase orders, or sales orders.
     * If the product is found in any of these tables, it returns true, indicating that the product is in use.
     * Otherwise, it returns false.
     */
    public function isUsedInTransactions()
    {
        return DB::table('stock_movements')
            ->where('product_id', $this->id)
            ->exists()
            || DB::table('stock_adjustment_details')
            ->where('product_id', $this->id)
            ->exists()
            || DB::table('purchase_order_details')
            ->where('product_id', $this->id)
            ->exists()
            || DB::table('sales_order_details')
            ->where('product_id', $this->id)
            ->exists();
    }
}
