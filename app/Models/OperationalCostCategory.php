<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationalCostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description'
    ];

}
