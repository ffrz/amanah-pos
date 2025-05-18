<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Customer extends Authenticatable
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'nis',
        'name',
        'phone',
        'address',
        'active',
        'balance',
        'active',
        'last_login_datetime',
        'last_activity_description',
        'last_activity_datetime'
    ];

    public static function activeCustomerCount()
    {
        return DB::select(
            'select count(0) as count from customers where active=1'
        )[0]->count;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'created_datetime')) {
                $model->created_datetime = current_datetime();
                $model->created_by_uid = Auth::id();
            }
            return true;
        });

        static::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_datetime')) {
                $model->updated_datetime = current_datetime();
                $model->updated_by_uid = Auth::id();
            }
            return true;
        });
    }
}
