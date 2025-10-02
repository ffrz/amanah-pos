<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class UserProfileFormatter extends BaseFormatter
{
    protected $mapping = [
        'username'  => 'Username',
        'name'      => 'Nama Pengguna',
        'active'    => 'Status'
    ];

    protected function formatValue(string $key, $value)
    {
        if ($key == 'active') {
            return $value ? 'Ya' : 'Tidak';
        }

        return parent::formatValue($key, $value);
    }
}
