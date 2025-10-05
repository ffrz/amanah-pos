<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class CashierSessionFormatter extends BaseFormatter
{
    protected $mapping = [
        'id'   => 'ID',
        'user_id' => 'ID Pengguna',
        'opened_at' => 'Waktu Mulai',
        'cashier_terminal' => 'Terminal Kasir',
        'opening_balance' => 'Saldo Awal',
        'opening_notes' => 'Catatan Awal',
        'closing_balance' => 'Saldo Akhir',
        'closing_notes' => 'Catatan Akhir',

        'created_at' => 'Waktu Dibuat',
        'created_by' => 'Dibuat Oleh',
    ];

    protected function formatValue(string $key, $value)
    {
        if ($key === 'opened_at' || $key == 'created_at') {
            return format_datetime(($value));
        }

        if ($key == 'cashier_terminal') {
            return $value['name'];
        }

        if ($key == 'opening_balance' || $key == 'closing_balance') {
            return 'Rp. ' . format_number($value);
        }

        return parent::formatValue($key, $value);
    }
}
