<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'date',
        'description',
        'amount',
        'notes'
    ];

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'date' => 'date', // hanya tanggal, tanpa waktu
            'description' => 'string',
            'amount' => 'decimal',
            'notes' => 'string',
        ];
    }
}
