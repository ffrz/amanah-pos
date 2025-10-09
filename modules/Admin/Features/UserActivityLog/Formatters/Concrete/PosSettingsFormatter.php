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

class PosSettingsFormatter extends BaseFormatter
{
    protected $mapping = [
        'default_payment_mode' => 'Default Pembayaran',
        'default_print_size'   => 'Default Ukuran Cetak',
        'after_payment_action' => 'Aksi Setelah Pembayaran',
        'foot_note'            => 'Foot Note',
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key === 'default_payment_mode') {
            return $value == 'cash' ? 'Tunai' : ($value == 'wallet' ? 'Wallet' : '');
        }

        if ($key === 'default_print_size') {
            return $value == '58mm' ? '58mm' : ($value == 'a4' ? 'A4' : '');
        }

        if ($key === 'after_payment_action') {
            return $value == 'new-order' ? 'Pesanan Baru' : ($value == 'print' ? 'Cetak' : 'Rincian');
        }

        return parent::formatValue($key, $value, $data);
    }
}
