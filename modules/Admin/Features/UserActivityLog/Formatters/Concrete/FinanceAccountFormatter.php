<?php

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

    protected function formatValue(string $key, $value)
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

        return parent::formatValue($key, $value);
    }
}
