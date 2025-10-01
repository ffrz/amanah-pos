<?php

namespace Modules\Admin\Features\UserActivityLog\Contracts;

interface MetaDataFormatterInterface
{
    /**
     * Memproses array metadata (dari JSON) menjadi format array [Label => Value] siap tampil.
     *
     * @param array $metaData Data metadata yang sudah di-decode.
     * @return array
     */
    public function format(array $metaData): array;
}
