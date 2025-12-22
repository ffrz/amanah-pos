<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 */

namespace App\Models;

class CustomerSetting extends BaseModel
{
    // Karena menggunakan composite primary key
    /**
     * @var array $primaryKey
     */
    protected $primaryKey = ['customer_id', 'key'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'key',
        'value',
    ];

    /**
     * Cache internal untuk menampung settings per customer
     * Struktur: [$customerId => [$key => $value]]
     */
    static $settings = [];
    static $initialized_customers = [];

    /**
     * Inisialisasi settings untuk customer tertentu
     */
    private static function _init($customerId)
    {
        if (!isset(static::$initialized_customers[$customerId])) {
            $items = self::where('customer_id', $customerId)->get();

            // Pastikan array disiapkan jika customer belum punya setting sama sekali
            static::$settings[$customerId] = [];

            foreach ($items as $item) {
                static::$settings[$customerId][$item->key] = $item->value;
            }

            static::$initialized_customers[$customerId] = true;
        }
    }

    /**
     * Ambil nilai setting berdasarkan customer_id dan key
     */
    public static function value($customerId, $key, $default = null)
    {
        static::_init($customerId);
        return isset(static::$settings[$customerId][$key])
            ? static::$settings[$customerId][$key]
            : $default;
    }

    /**
     * Ambil semua settings milik customer tertentu
     */
    public static function values($customerId)
    {
        static::_init($customerId);
        return static::$settings[$customerId] ?? [];
    }

    /**
     * Simpan atau update setting customer
     */
    public static function setValue($customerId, $key, $value)
    {
        self::updateOrCreate(
            ['customer_id' => $customerId, 'key' => $key],
            ['value' => $value]
        );

        // Update cache internal
        static::$settings[$customerId][$key] = $value;
        static::$initialized_customers[$customerId] = true;
    }

    /**
     * Reset cache untuk customer tertentu agar di-load ulang dari DB pada request berikutnya
     */
    public static function refreshForCustomer($customerId)
    {
        unset(static::$initialized_customers[$customerId]);
        unset(static::$settings[$customerId]);
        static::_init($customerId);
    }

    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->primaryKey as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }

        return $query;
    }
}
