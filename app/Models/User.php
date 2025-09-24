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

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        HasFactory,
        Notifiable,
        HasRoles;

    public const Type_SuperUser = 'super_user';
    public const Type_StandardUser = 'standard_user';

    public const Types = [
        self::Type_SuperUser => 'Super User',
        self::Type_StandardUser => 'Standar User',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'active',
        'password',
        'type',  // TODO: remove jika sudah integrasi full pakai spatie laravel
        'last_login_datetime',
        'last_activity_description',
        'last_activity_datetime',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password'                  => 'hashed',
            'name'                      => 'string',
            'username'                  => 'string',
            'active'                    => 'boolean',
            'type'                      => 'string',
            'last_login_datetime'       => 'datetime',
            'last_activity_description' => 'string',
            'last_activity_datetime'    => 'datetime',
        ];
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

    public static function activeUserCount()
    {
        return DB::select(
            'select count(0) as count from users where active=1'
        )[0]->count;
    }

    public function findForAuth($username)
    {
        return $this->where('username', $username)->first();
    }

    public function getActiveSessionCashierAccount()
    {
        // 1. Cari sesi kasir yang sedang aktif untuk pengguna ini
        // Sesi aktif adalah yang is_closed = false
        $activeSession = CashierSession::where('user_id', $this->id)
            ->where('is_closed', false)
            ->first();

        // 2. Jika tidak ada sesi aktif, kembalikan null
        if (!$activeSession) {
            return null;
        }

        // 3. Muat relasi cashierTerminal dan financeAccount
        // Gunakan with() untuk eager loading agar lebih efisien
        $activeSession->load('cashierTerminal.financeAccount');

        // 4. Periksa apakah relasi berhasil dimuat dan kembalikan akun kas
        if ($activeSession->cashierTerminal && $activeSession->cashierTerminal->financeAccount) {
            return $activeSession->cashierTerminal->financeAccount;
        }

        return null;
    }
}
