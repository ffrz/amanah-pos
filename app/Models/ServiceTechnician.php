<?php

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTechnician extends BaseModel
{
    use HasDocumentVersions,
        SoftDeletes;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'phone',
        'address',
        'email',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relasi ke User (Sistem Login)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Daftar order yang ditangani teknisi ini
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'technician_id');
    }

    public static function generateCode(): string
    {
        $lastId = self::max('id') ?? 0;
        $nextId = $lastId + 1;
        $code = str_pad($nextId, 2, '0', STR_PAD_LEFT);
        $prefix = Setting::value('service.technician.code-prefix', 'ST-');
        return $prefix . $code;
    }
}
