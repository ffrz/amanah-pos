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

class UserFormatter extends BaseFormatter
{
    protected $mapping = [
        'id'        => 'ID',
        'username'  => 'Username',
        'name'      => 'Nama Pengguna',
        'active'    => 'Aktif',
        'type'      => 'Jenis Akun',
        'roles'     => 'Peran',
        'password'  => 'Kata Sandi',
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key == 'active') {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($key == 'type') {
            return $value == 'standard_user' ? 'Standar' : 'Super User';
        }

        if ($key == 'roles') {
            if (!$value) return '';

            $roles = [];
            foreach ($value as $item) {
                foreach ($item as $k => $v) {
                    if ($k == 'name')
                        $roles[] = $v;
                }
            }
            return join(',', $roles);
        }

        return parent::formatValue($key, $value, $data);
    }
}
