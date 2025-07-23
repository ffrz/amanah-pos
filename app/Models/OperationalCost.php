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

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'date' => 'date', // hanya tanggal, tanpa waktu
            'description' => 'string',
            'amount' => 'decimal',
            'notes' => 'string',
            'created_by_uid' => 'integer',
            'updated_by_uid' => 'integer',
            'created_datetime' => 'datetime',
            'updated_datetime' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
