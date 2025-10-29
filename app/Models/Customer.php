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
        'credit_limit',
        'credit_allowed',
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
            'credit_limit' => 'decimal:2',
            'credit_allowed' => 'boolean',
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

    /**
     * Menghitung total pelanggan yang aktif secara bisnis.
     */
    public static function activeCustomerCount(): int
    {
        // Secara otomatis mengecualikan data soft-deleted
        return Customer::where('active', 1)->count();
    }

    /**
     * Menghitung total saldo wallet/akun (Piutang bersih - Deposit bersih) dari pelanggan aktif.
     */
    public static function totalActiveWalletBalance(): float
    {
        // Menggunakan sum() untuk menghitung total saldo
        return static::where('active', 1)->sum('wallet_balance');
    }

    /**
     * Menghitung total saldo utang piutang dari pelanggan aktif.
     */
    public static function totalActiveBalance(): float
    {
        // Menggunakan sum() untuk menghitung total saldo
        return static::where('active', 1)->sum('balance');
    }

    /**
     * Menghitung total Utang Pelanggan Aktif (sesuai konvensi: wallet_balance > 0).
     *
     * Catatan: Query asli Anda menggunakan wallet_balance < 0 untuk Debt.
     * Saya mengacu pada konvensi standar akuntansi (Piutang > 0, Utang < 0) yang kita sepakati.
     * Saya akan mengoreksi query ini berdasarkan konvensi yang Anda set sebelumnya (wallet_balance < 0 = Debt).
     */
    public static function totalActiveDebt(): float
    {
        return Customer::where('active', 1)
            ->where('wallet_balance', '<', 0)
            ->sum('wallet_balance');
    }

    /**
     * Menghitung total Piutang Pelanggan Aktif (sesuai konvensi: wallet_balance > 0).
     */
    public static function totalActiveCredit(): float
    {
        return Customer::where('active', 1)
            ->where('wallet_balance', '>', 0)
            ->sum('wallet_balance');
    }

    /**
     * Mendapatkan Top N pelanggan aktif berdasarkan saldo wallet/deposit tertinggi.
     *
     * @param int $limit Batas jumlah pelanggan teratas yang ditampilkan (default 5).
     * @return \Illuminate\Support\Collection
     */
    public static function getTopCustomersByWalletBalance(int $limit = 5): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('active', true)
            ->where('wallet_balance', '>', 0)
            ->select('id', 'name', 'wallet_balance')
            ->orderBy('wallet_balance', 'desc')
            ->limit($limit)
            ->get();
    }
}
