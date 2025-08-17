<?php

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

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        HasFactory,
        Notifiable;

    public const Role_Admin = 'admin';
    public const Role_Cashier = 'cashier';
    public const Role_Owner = 'owner';

    // Display role di hardcode saja, tidak diambil dari translations
    public const Roles = [
        self::Role_Admin => 'Administrator',
        self::Role_Cashier => 'Kasir',
        self::Role_Owner => 'Owner',
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
        'role',  // TODO: remove jika sudah integrasi full pakai spatie laravel
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
            'role'                      => 'string',
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
}
