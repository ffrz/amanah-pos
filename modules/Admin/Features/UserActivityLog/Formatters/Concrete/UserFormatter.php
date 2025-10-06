<?php

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
