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
        HasApiTokens;

    public $timestamps = false;

    protected $fillable = [
        'nis',
        'password',
        'name',
        'parent_name',
        'phone',
        'address',
        'balance',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nis' => 'string',
            'password' => 'hashed',
            'name' => 'string',
            'parent_name' => 'string',
            'phone' => 'string',
            'address' => 'string',
            'balance' => 'decimal:2',
            'active' => 'boolean',
            'last_login_datetime' => 'datetime',
            'last_activity_description' => 'string',
            'last_activity_datetime' => 'datetime',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

    public static function activeCustomerCount()
    {
        return DB::select(
            'select count(0) as count from customers where active=1'
        )[0]->count;
    }

    public static function totalActiveBalance()
    {
        return DB::select(
            'select sum(balance) as sum from customers where active=1'
        )[0]->sum;
    }

    public static function totalActiveDebt()
    {
        return DB::select(
            'select sum(balance) as sum from customers where active=1 and balance < 0'
        )[0]->sum;
    }

    public static function totalActiveCredit()
    {
        return DB::select(
            'select sum(balance) as sum from customers where active=1 and balance > 0'
        )[0]->sum;
    }
}
