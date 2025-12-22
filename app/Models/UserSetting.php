<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 */

namespace App\Models;

class UserSetting extends BaseModel
{
    // Karena menggunakan composite primary key
    /**
     * @var array $primaryKey
     */
    protected $primaryKey = ['user_id', 'key'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Cache internal untuk menampung settings per user
     * Struktur: [$userId => [$key => $value]]
     */
    static $settings = [];
    static $initialized_users = [];

    /**
     * Inisialisasi settings untuk user tertentu
     */
    private static function _init($userId)
    {
        if (!isset(static::$initialized_users[$userId])) {
            $items = self::where('user_id', $userId)->get();

            // Pastikan array disiapkan jika user belum punya setting sama sekali
            static::$settings[$userId] = [];

            foreach ($items as $item) {
                static::$settings[$userId][$item->key] = $item->value;
            }

            static::$initialized_users[$userId] = true;
        }
    }

    /**
     * Ambil nilai setting berdasarkan user_id dan key
     */
    public static function value($userId, $key, $default = null)
    {
        static::_init($userId);
        return isset(static::$settings[$userId][$key])
            ? static::$settings[$userId][$key]
            : $default;
    }

    /**
     * Ambil semua settings milik user tertentu
     */
    public static function values($userId)
    {
        static::_init($userId);
        return static::$settings[$userId] ?? [];
    }

    /**
     * Simpan atau update setting user
     */
    public static function setValue($userId, $key, $value)
    {
        self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );

        // Update cache internal
        static::$settings[$userId][$key] = $value;
        static::$initialized_users[$userId] = true;
    }

    /**
     * Reset cache untuk user tertentu agar di-load ulang dari DB pada request berikutnya
     */
    public static function refreshForUser($userId)
    {
        unset(static::$initialized_users[$userId]);
        unset(static::$settings[$userId]);
        static::_init($userId);
    }

    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->primaryKey as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }

        return $query;
    }
}
