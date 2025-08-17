<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends BaseModel
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

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
