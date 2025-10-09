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

use App\Models\FinanceAccount;

class FinanceAccountFormatter extends BaseFormatter
{
    protected $mapping = [
        "id" => 'ID',
        "name" => "Nama Akun",
        "type" => "Jenis",
        "bank" => "Bank",
        "holder" => "Atas Nama",
        "number" => "No Rekening",
        "balance" => "Saldo",
        "has_wallet_access" => 'Tampilkan di Wallet',
        "show_in_pos_payment" => 'Tampilkan di Penjualan',
        "show_in_purchasing_payment" => 'Tampilkan di Pembelian',
        "notes" => "Catatan",
        "active" => "Aktif",
        "created_at" => "Waktu Dibuat",
        "created_by" => 'ID User',
    ];

    protected function formatValue(string $key, $value, $data)
    {
        if (in_array($key, ['active', 'has_wallet_access', 'show_in_pos_payment', 'show_in_purchasing_payment'])) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($key == 'type') {
            return FinanceAccount::Types[$value];
        }

        if ($key == 'balance') {
            return 'Rp.' . format_number($value);
        }

        return parent::formatValue($key, $value, $data);
    }
}
