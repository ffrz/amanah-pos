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

namespace Modules\Admin\Http\Controllers\Reports;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\CommonDataService;

class SalesOrderRecapController extends BaseController
{
    protected string $default_title = 'Laporan Rekapitulasi Penjualan';

    protected $primary_columns = [
        'code' => 'Kode',
        'date' => 'Tanggal',
        'customer' => 'Pelanggan',
        'total_item' => 'Total Item',
        'total_price' => 'Sub Total (Rp)',
        'grand_total' => 'Total (Rp)',
        // 'total_cash' => 'Tunai (Rp)',
        // 'total_credit' => 'Kredit (Rp)',
    ];

    protected $optional_columns = [];

    protected $initial_columns = [];

    public function __construct(
        protected CommonDataService $commonDataService,
    ) {
        parent::__construct();
        $this->initial_columns = array_keys($this->primary_columns);
    }

    public function index()
    {
        return $this->generateIndexResponse('reports/sales-order-recap/Index', [
            // 'categories' => $this->commonDataService->getProductCategories(),
            // 'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
        ]);

        $q = SalesOrder::query();

        return $this->generateReport(
            'modules.admin.pages.reports.sales-order-recap.list',
            $data,
            $q
        );
    }

    // File: SalesOrderRecapController.php (processQuery method)

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $queryColumns, $data)
    {
        // Kolom yang dipilih hanya dari tabel sales_orders
        $subqueryTotalItems = '(
            SELECT SUM(quantity) 
            FROM sales_order_details as sod 
            WHERE sod.order_id = sales_orders.id AND sod.return_id IS NULL
        )';

        // Kolom yang dipilih hanya dari tabel sales_orders
        $selects = [
            'sales_orders.id',
            'sales_orders.code',
            DB::raw('DATE(sales_orders.datetime) as date'),
            'sales_orders.customer_code',
            'sales_orders.customer_name',
            'sales_orders.total_price',
            'sales_orders.total_discount',
            'sales_orders.total_tax',
            'sales_orders.grand_total',
            DB::raw("COALESCE({$subqueryTotalItems}, 0) as total_item"),
            // TODO: uncomment / ganti kalau field sudah tersedia
            // 'sales_orders.total_initial_cash_payment',
            // 'sales_orders.grand_total - sales_orders.total_initial_cash_payment as total_credit',
        ];


        $filter = $data['filter'];
        if (!empty($filter['start_date'])) {
            $q->whereDate('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        $q->select($selects);
        $q->where('status', \App\Models\SalesOrder::Status_Closed);

        $sortOptions = request('sortOptions');
        if (is_array($sortOptions)) {
            foreach ($sortOptions as $option) {
                $q->orderBy($option['column'], $option['order']);
            }
        }

        return $q->get();
    }
}
