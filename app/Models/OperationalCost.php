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
            'date' => 'string',
            'description' => 'string',
            'amount' => 'decimal:2',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'string',
            'updated_at' => 'string',
        ];
    }

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }
}
