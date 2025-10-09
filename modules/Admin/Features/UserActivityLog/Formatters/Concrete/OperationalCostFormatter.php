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

class OperationalCostFormatter extends BaseFormatter
{
    protected $mapping = [
        'id'          => 'ID',
        'date'        => 'Tanggal',
        'finance_account_id'   => 'ID Akun Kas',
        'finance_account_name' => 'Nama Akun Kas',
        'category_id'   => 'ID Kategori',
        'category_name' => 'Nama Kategori',
        'description' => 'Deskripsi',
        'amount'      => 'Jumlah',
        'image_path'  => 'Attachment',
        'notes'       => 'Catatan',

        'created_at' => 'Waktu Dibuat',
        'updated_at' => 'Waktu Diperbarui',
        'created_by_username' => 'Dibuat Oleh',
        'updated_by_username' => 'Diperbarui Oleh',
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key === 'date') {
            return format_date(($value));
        }

        if ($key == 'amount') {
            return 'Rp. ' . format_number($value);
        }

        return parent::formatValue($key, $value, $data);
    }
}
