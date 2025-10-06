<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class SupplierFormatter extends BaseFormatter
{
    // TODO: Lengkapi data ini
    protected $mapping = [
        "id" => 'ID',
        "name" => "Nama Pemasok",
        "phone_1" => "No Telepon 1",
        "phone_2" => "No Telepon 2",
        "phone_3" => "No Telepon 3",
        "address" => "Alamat",
        "return_address" => "Alamat Retur",
        'bank_account_1' => "No Rek 1",
        'bank_account_2' => "No Rek 2",
        "url_1" => 'URL 1',
        "url_2" => 'URL 2',
        "active" => "Aktif",
    ];

    protected function formatValue(string $key, $value, $data = [])
    {
        if ($key == 'active') {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($key == 'bank_account_1') {
            $value = $data['bank_account_name_1'];
            return $data['bank_account_name_1'] . ' a.n. ' .
                $data['bank_account_holder_1'] . ' ' .
                $data['bank_account_name_1'];
        }

        if ($key == 'bank_account_2') {
            $value = $data['bank_account_name_2'];
            return $data['bank_account_name_2'] . ' a.n. ' .
                $data['bank_account_holder_2'] . ' ' .
                $data['bank_account_name_2'];
        }

        return parent::formatValue($key, $value, $data);
    }
}
