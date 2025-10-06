<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class CashierTerminalFormatter extends BaseFormatter
{
    // TODO: Lengkapi data ini
    protected $mapping = [
        "id" => 'ID',
        "name" => "Nama Terminal",
        "active" => "Aktif",
    ];

    protected function formatValue(string $key, $value, $data)
    {
        if (in_array($key, ['active'])) {
            return $value ? 'Ya' : 'Tidak';
        }

        return parent::formatValue($key, $value, $data);
    }
}
