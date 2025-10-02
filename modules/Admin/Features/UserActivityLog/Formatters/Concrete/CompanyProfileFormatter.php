<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class CompanyProfileFormatter extends BaseFormatter
{
    protected $mapping = [
        'name'      => 'Nama Perusahaan',
        'headline'  => 'Headline',
        'phone'     => 'No Telepon',
        'address'   => 'Alamat',
        'logo_path' => 'Logo',
    ];
}
