<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'active',
        'bank_account_number',
        'return_address'
    ];

    protected function casts(): array
    {
        return [
            'name'                 => 'string',
            'phone'                => 'string',
            'address'              => 'string',
            'active'               => 'boolean',
            'bank_account_number'  => 'string',
            'return_address'       => 'string',
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

    public static function activeSupplierCount()
    {
        return DB::select(
            'select count(0) as count from suppliers where active = 1'
        )[0]->count;
    }
}
