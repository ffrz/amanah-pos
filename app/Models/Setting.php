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

class Setting extends BaseModel
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'lastmod_datetime',
        'lastmod_user_id',
        'lastmod_username',
    ];

    static $settings = [];
    static $is_initialized = false;

    private static function _init()
    {
        if (!static::$is_initialized) {
            $items = Setting::all();
            foreach ($items as $item) {
                static::$settings[$item->key] = $item->value;
            }
            static::$is_initialized = true;
        }
    }

    public static function value($key, $default = null)
    {
        static::_init();
        return isset(static::$settings[$key]) ? static::$settings[$key] : $default;
    }

    public static function values()
    {
        static::_init();
        return static::$settings;
    }

    public static function setValue($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$settings[$key] = $value;
    }
}
