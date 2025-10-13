<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class Customer extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        HasFactory,
        Notifiable,
        HasDocumentVersions,
        HasApiTokens,
        SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'password',
        'name',
        'type',
        'phone',
        'address',
        'wallet_balance',
        'balance',
        'default_price_type',
        'active',
        'last_login_datetime',
        'last_activity_description',
        'last_activity_datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const Type_Category_3 = 'category3';
    public const Type_Category_2 = 'category2';
    public const Type_Category_1 = 'category1';
    public const Type_Staff      = 'staff';
    public const Type_General    = 'general';

    public const Types = [
        self::Type_Category_3 => 'Kategori 3',
        self::Type_Category_2 => 'Kategori 2',
        self::Type_Category_1 => 'Kategori 1',
        self::Type_Staff   => 'Staff',
        self::Type_General => 'Umum',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => 'string',
            'password' => 'hashed',
            'name' => 'string',
            'type' => 'string',
            'parent_name' => 'string',
            'phone' => 'string',
            'address' => 'string',
            'wallet_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'active' => 'boolean',
            'default_price_type' => 'string',
            'last_login_datetime' => 'datetime',
            'last_activity_description' => 'string',
            'last_activity_datetime' => 'datetime',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected $appends = [
        'type_label',
        'default_price_type_label',
    ];

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '';
    }

    public function getDefaultPriceTypeLabelAttribute()
    {
        return Product::PriceTypes[$this->default_price_type] ?? '';
    }

    public function setLastLogin()
    {
        $this->last_login_datetime = now();
        $this->save();
    }

    public function setLastActivity($description)
    {
        $this->last_activity_description = $description;
        $this->last_activity_datetime = now();
        $this->save();
    }

    public static function activeCustomerCount()
    {
        return DB::select(
            'select count(0) as count from customers where active=1'
        )[0]->count;
    }

    public static function totalActiveWalletBalance()
    {
        return DB::select(
            'select sum(wallet_balance) as sum from customers where active=1'
        )[0]->sum;
    }

    public static function totalActiveDebt()
    {
        return DB::select(
            'select sum(wallet_balance) as sum from customers where active=1 and wallet_balance < 0'
        )[0]->sum;
    }

    public static function totalActiveCredit()
    {
        return DB::select(
            'select sum(wallet_balance) as sum from customers where active=1 and wallet_balance > 0'
        )[0]->sum;
    }
}
