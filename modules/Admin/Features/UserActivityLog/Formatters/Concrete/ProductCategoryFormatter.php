<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class ProductCategoryFormatter extends BaseFormatter
{
    protected $mapping = [
        'id'          => 'ID Kategori',
        'name'        => 'Nama Kategori',
        'description' => 'Deskripsi',
        'created_at'  => 'Waktu Dibuat',
        'created_by'  => 'ID Pembuat',
    ];
}
