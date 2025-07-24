<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the author of the product category.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    /**
     * Get the updater of the product category.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'created_by_uid' => 'integer',
            'updated_by_uid' => 'integer',
            'created_datetime' => 'datetime',
            'updated_datetime' => 'datetime',
        ];
    }
}
