<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCost extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'date',
        'description',
        'amount',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'date' => 'date',
            'description' => 'string',
            'amount' => 'decimal:2',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }
}
