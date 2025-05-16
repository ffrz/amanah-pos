<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class WashService extends Model
{
    // use HasFactory;

    // Pertimbangkan untuk menggunakan tipe dihard code atau dinamis dengan kategori
    // public const TYPE_WASH = 'wash';
    // public const TYPE_DETAIL = 'detailing';
    // public const TYPE_INTERIOR = 'interior';

    protected $fillable = [
        'name', 'duration', 'price', 'description', 'active',
        // 'type'
    ];

}
