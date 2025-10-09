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

namespace App\Helpers;

class ArrayDiffHelper
{
    /**
     * Menghitung perbedaan antara atribut lama dan baru dari sebuah model.
     * Digunakan untuk membuat metadata log aktivitas.
     *
     * @param array $originalAttributes Atribut asli model.
     * @param array $dirtyAttributes Atribut yang diubah.
     * @return array Metadata perubahan.
     */
    public static function createMetadata(array $originalAttributes, array $dirtyAttributes): array
    {
        $metadata = [];
        foreach (array_keys($dirtyAttributes) as $key) {
            $metadata[$key] = [
                'old' => $originalAttributes[$key] ?? null,
                'new' => $dirtyAttributes[$key],
            ];
        }

        return $metadata;
    }
}
