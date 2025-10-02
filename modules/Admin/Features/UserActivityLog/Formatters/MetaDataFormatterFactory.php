<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\CompanyProfileFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\DefaultFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\ProductCategoryFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\UserProfileFormatter;

class MetaDataFormatterFactory
{
    // Mapping nama formatter dengan class Formatter
    protected static array $formatterMap = [
        'product-category' => ProductCategoryFormatter::class,
        'company-profile' => CompanyProfileFormatter::class,
        'user-profile' => UserProfileFormatter::class,
    ];

    /**
     * Membuat instance Formatter yang sesuai berdasarkan nama formatter log.
     * * @param string $formatter formatter dari metadata.
     * @return MetaDataFormatterInterface
     * @throws \InvalidArgumentException Jika formatter tidak ditemukan.
     */
    public static function create(string $formatter): MetaDataFormatterInterface
    {
        if (isset(self::$formatterMap[$formatter])) {
            $formatterClass = self::$formatterMap[$formatter];
            return new $formatterClass();
        }

        // Kembalikan Formatter default jika tidak ada, atau lempar exception
        throw new \InvalidArgumentException("No formatter found for {$formatter}");
        // return new DefaultFormatter(); // Alternatif: gunakan DefaultFormatter
    }
}
