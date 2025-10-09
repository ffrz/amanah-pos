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

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class UserRoleFormatter extends BaseFormatter
{
    protected $mapping = [
        'name'        => 'Nama Peran',
        'description' => 'Deskripsi',
        'permissions' => 'Hak Akses'
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key == 'permissions') {
            $result = [];
            foreach ($value as $item) {
                foreach ($item as $k => $v) {
                    if ($k == 'label') {
                        $result[] = $v;
                    }
                }
            }
            return join(', ', $result);
        }

        return parent::formatValue($key, $value, $data);
    }
}
