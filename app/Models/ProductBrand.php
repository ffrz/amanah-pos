<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBrand extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'active'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
