<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Features\UserActivityLog\Formatters;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\CashierSessionFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\CashierTerminalFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\CompanyProfileFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\CustomerFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\DefaultFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\FinanceAccountFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\OperationalCostCategoryFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\OperationalCostFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\PosSettingsFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\ProductCategoryFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\SupplierFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\UserFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\UserProfileFormatter;
use Modules\Admin\Features\UserActivityLog\Formatters\Concrete\UserRoleFormatter;

class MetaDataFormatterFactory
{
    // Mapping nama formatter dengan class Formatter
    protected static array $formatterMap = [
        'product-category' => ProductCategoryFormatter::class,
        'company-profile' => CompanyProfileFormatter::class,
        'pos-settings' => PosSettingsFormatter::class,
        'user-profile' => UserProfileFormatter::class,
        'user-role' => UserRoleFormatter::class,
        'user' => UserFormatter::class,
        'operational-cost' => OperationalCostFormatter::class,
        'operational-cost-category' => OperationalCostCategoryFormatter::class,
        'finance-account' => FinanceAccountFormatter::class,
        'finance-transaction' => DefaultFormatter::class,
        'cashier-session' => CashierSessionFormatter::class,
        'cashier-terminal' => CashierTerminalFormatter::class,
        'supplier' => SupplierFormatter::class,
        'customer' => CustomerFormatter::class,
        'product' => DefaultFormatter::class,
        'stock-adjustment' => DefaultFormatter::class,
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
