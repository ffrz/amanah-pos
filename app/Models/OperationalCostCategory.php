<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCostCategory extends BaseModel
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
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
