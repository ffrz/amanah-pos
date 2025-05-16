<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'date', 'description', 'amount', 'notes'
    ];

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }
}
