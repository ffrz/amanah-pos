<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class UserRoleFormatter extends BaseFormatter
{
    protected $mapping = [
        'name'        => 'Nama Peran',
        'description' => 'Deskripsi',
        'permissions' => 'Hak Akses'
    ];

    protected function formatValue(string $key, $value)
    {
        if ($key == 'permissions') {
            $result = [];
            foreach ($value as $item) {
                foreach ($item as $k => $v) {
                    if ($k == 'label') {
                        $result[] = $v;
                    }
                }
            }
            return join(', ', $result);
        }

        return parent::formatValue($key, $value);
    }
}
