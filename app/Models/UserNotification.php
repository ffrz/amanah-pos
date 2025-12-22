<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class UserNotification extends BaseModel
{
    use HasUuids; // Mengaktifkan otomatisasi UUID

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',      // Mengonversi JSON di DB menjadi array PHP otomatis
        'read_at' => 'datetime',
    ];

    // --- Relationships ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes (Untuk Query yang Lebih Bersih) ---

    /**
     * Ambil notifikasi yang belum dibaca
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Ambil notifikasi yang sudah dibaca
     */
    public function scopeRead(Builder $query): void
    {
        $query->whereNotNull('read_at');
    }

    // --- Helper Methods ---

    /**
     * Tandai sebagai sudah dibaca
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Cek apakah notifikasi sudah dibaca
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Static helper untuk mengirim notifikasi dengan cepat
     */
    public static function send($userId, $title, $message, $type = 'info', array $data = [])
    {
        return self::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'data'    => $data,
        ]);
    }
}