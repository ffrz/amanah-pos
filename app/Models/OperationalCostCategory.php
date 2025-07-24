<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }
}
