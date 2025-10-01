<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\ProductCategoryFormatter;

class MetaDataFormatterFactory
{
    // Mapping antara kategori/nama log dengan class Formatter
    protected static array $formatterMap = [
        'product-category' => ProductCategoryFormatter::class,
        // 'Supplier' => SupplierFormatter::class, // Class SupplierFormatter harus dibuat
        // ... Tambahkan mapping lain di sini ...
    ];

    /**
     * Membuat instance Formatter yang sesuai berdasarkan kategori log.
     * * @param string $logCategory Nilai dari kolom 'category' di UserActivityLog.
     * @return MetaDataFormatterInterface
     * @throws \InvalidArgumentException Jika formatter tidak ditemukan.
     */
    public static function create(string $logCategory): MetaDataFormatterInterface
    {
        if (isset(self::$formatterMap[$logCategory])) {
            $formatterClass = self::$formatterMap[$logCategory];
            return new $formatterClass();
        }

        // Kembalikan Formatter default jika tidak ada, atau lempar exception
        throw new \InvalidArgumentException("No formatter found for category: {$logCategory}");
        // return new DefaultFormatter(); // Alternatif: gunakan DefaultFormatter
    }
}
