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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

abstract class BaseModel extends Model
{
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk saat model sedang dibuat (creating)
        static::creating(function ($model) {
            $now = now();
            $auth = Auth::user();

            if ($auth && $model->hasColumn('created_by')) {
                $model->created_by = $auth->id;
            }

            if ($model->hasColumn('created_at')) {
                $model->created_at = $now;
            }
        });

        // Event listener untuk saat model sedang diperbarui (updating)
        static::updating(function ($model) {
            $now = now();
            $auth = Auth::user();

            if ($model->hasColumn('updated_at')) {
                $model->updated_at = $now;
            }

            if ($auth && $model->hasColumn('updated_by')) {
                $model->updated_by = $auth->id;
            }
        });

        // Event saat deleting (soft delete)
        static::deleting(function ($model) {
            if (property_exists($model, 'forceDeleting') && $model->forceDeleting === true) {
                return;
            }

            // Hanya lakukan soft-delete jika kolomnya ada
            if ($model->hasColumn('deleted_at')) {
                $now  = now();
                $auth = Auth::user();

                if ($model->hasColumn('deleted_at')) {
                    $model->deleted_at = $now;
                }

                if ($auth && $model->hasColumn('deleted_by')) {
                    $model->deleted_by = $auth->id;
                }

                $model->renameUniqueColumnsOnDelete();

                $model->saveQuietly();
                return false; // Batalkan hard delete (soft delete saja)
            }

            return;
        });
    }

    /**
     * Mengubah nama kolom unik yang ditentukan, memastikan panjangnya tidak melebihi batas.
     */
    protected function renameUniqueColumnsOnDelete(): void
    {
        // Mengambil daftar kolom dari properti Model.
        $columnsToRename = ['username', 'code', 'name'];

        // Suffix yang akan ditambahkan. Menggunakan ID dan Timestamp untuk menjamin keunikan global.
        // Format: __DEL:ID:TIMESTAMP
        $suffix = '__DEL:' . $this->id . ':' . time();
        $maxLength = 255; // Asumsi umum lebar kolom VARCHAR(255)

        foreach ($columnsToRename as $column) {
            // Cek apakah model memiliki atribut untuk kolom ini
            if (isset($this->attributes[$column])) {
                $originalValue = $this->attributes[$column];

                // Hitung panjang maksimum value asli yang boleh digunakan
                $maxOriginalLength = $maxLength - strlen($suffix);

                if (strlen($originalValue) > $maxOriginalLength) {
                    // Potong nilai asli jika terlalu panjang.
                    $truncatedValue = substr($originalValue, 0, $maxOriginalLength);
                    $this->attributes[$column] = $truncatedValue . $suffix;
                } else {
                    // Jika cukup, tambahkan saja suffix
                    $this->attributes[$column] = $originalValue . $suffix;
                }

                // kita asumsikan hanya 1 kolom unik, jika ada perubahan kita harus sesuaikan
                // atau bisa dibuat custom rename handler di subclass
                break;
            }
        }
    }

    /**
     * Memeriksa apakah model memiliki kolom tertentu.
     * Menggunakan cache untuk performa.
     *
     * @param string $column Nama kolom yang ingin diperiksa.
     * @return bool
     */
    protected static function hasColumn(string $column): bool
    {
        static $columnsCache = [];

        $table = (new static)->getTable();

        if (!isset($columnsCache[$table])) {
            $columnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($column, $columnsCache[$table]);
    }

    /**
     * Relasi dengan model User untuk creator (created_by).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi dengan model User untuk updater (updated_by).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi dengan model User untuk deleter (deleted_by).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function replicate(?array $except = null)
    {
        $defaults = [
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'deleted_at',
            'deleted_by',
        ];
        return parent::replicate($except ? array_unique(array_merge($except, $defaults)) : $defaults);
    }
}
