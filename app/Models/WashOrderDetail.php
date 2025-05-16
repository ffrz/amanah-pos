<?php

namespace App\Models;

class WashOrderDetail extends \Illuminate\Database\Eloquent\Model
{
    // Disable auto-incrementing karena kita pakai composite key
    public $incrementing = false;

    // Tipe primary key bukan integer auto-increment
    protected $keyType = 'int';

    // Nama tabel
    protected $table = 'wash_order_details';

    // Mass assignment
    protected $fillable = [
        'order_id',
        'id', // ini adalah bagian dari composite key
        'operator_id',
        'service_id',
        'price',
        'notes',
    ];

    // Jika kamu pakai timestamps, tambahkan ini
    public $timestamps = false;

    // Relasi ke WashOrder
    public function order()
    {
        return $this->belongsTo(WashOrder::class, 'order_id');
    }

    // Relasi ke User (operator)
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Relasi ke Service
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
