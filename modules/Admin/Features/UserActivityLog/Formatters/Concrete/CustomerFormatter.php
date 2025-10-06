<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class CustomerFormatter extends BaseFormatter
{
    // TODO: Lengkapi data ini
    protected $mapping = [
        "id" => 'ID',
        "username" => "Kode Pelanggan",
        "name" => "Nama Pelanggan",
        "phone" => "No Telepon",
        "address" => "Alamat",
        "wallet_balance" => "Saldo Wallet",
        "balance" => "Utang / Piutang",
        "active" => "Aktif",
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key == 'active') {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($key == 'balance' || $key == 'wallet_balance') {
            return 'Rp. ' . format_number(($value));
        }

        return parent::formatValue($key, $value, $data);
    }
}
