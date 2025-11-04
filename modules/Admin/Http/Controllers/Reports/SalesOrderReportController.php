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
use Illuminate\Validation\Rule; // Tambahkan import Rule
use Modules\Admin\Services\CommonDataService;

class SalesOrderReportController extends BaseController
{
    protected string $default_title = 'Laporan Penjualan';

    protected array $views = [
        'report_1' => 'modules.admin.pages.reports.sales-order-recap.list',
        'report_2' => 'modules.admin.pages.reports.sales-order-detail.list',
    ];

    protected array $templates = [
        'report_1' => [
            'value' => 'report_1',
            'label' => 'Laporan Rekapitulasi Penjualan',
        ],
        'report_2' => [
            'value' => 'report_2',
            'label' => 'Laporan Rincian Penjualan',
        ],
    ];

    protected $primary_columns = [
        'code' => 'Kode',
        'datetime' => 'Tanggal',
        'customer' => 'Pelanggan',
        'total_item' => 'Total Item',
        'total_price' => 'Sub Total (Rp)',
        'grand_total' => 'Total (Rp)',
    ];

    public function __construct(
        protected CommonDataService $commonDataService,
    ) {
        parent::__construct();
        $this->initial_columns = array_keys($this->primary_columns);
    }

    public function index()
    {
        return $this->generateIndexResponse('reports/sales-order/Index', [
            'customers' => $this->commonDataService->getCustomers(['id', 'code', 'name'], false),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            // 'filter.customer_id' => ['nullable', 'integer'],
        ]);

        $q = SalesOrder::query();

        $template = $this->views[$data['template']];

        return $this->generateReport(
            $template,
            $data,
            $q
        );
    }

    /**
     * Logika query disatukan di sini.
     * Perbedaan logika (eager loading dan kolom tanggal) dihandle berdasarkan $templateId.
     */
    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $queryColumns, $data)
    {
        $filter = $data['filter'];

        if (!empty($filter['start_date'])) {
            $q->whereDate('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->whereDate('datetime', '<=', $filter['end_date']);
        }

        if (!empty($filter['customer_id'])) {
            $q->where('customer_id', $filter['customer_id']);
        }

        $q->where('status', \App\Models\SalesOrder::Status_Closed);

        $subqueryTotalItems = '(
            SELECT SUM(quantity) 
            FROM sales_order_details as sod 
            WHERE sod.order_id = sales_orders.id AND sod.return_id IS NULL
        )';

        $selects = [
            'sales_orders.id',
            'sales_orders.code',
            'sales_orders.customer_code',
            'sales_orders.customer_name',
            'sales_orders.total_price',
            'sales_orders.grand_total',
            DB::raw("COALESCE({$subqueryTotalItems}, 0) as total_item"),
            'sales_orders.total_discount',
            'sales_orders.total_tax',
        ];

        if ($data['template'] === 'report_2') {
            $q->with([
                'details' => function ($r) {
                    $r->whereNull('return_id')
                        ->select(['order_id', 'product_name', 'product_barcode', 'quantity', 'product_uom', 'price', 'subtotal_price', 'notes']);
                }
            ]);
            $selects[] = 'sales_orders.datetime';
        } else {
            $selects[] = DB::raw('DATE(sales_orders.datetime) as date');
        }

        $q->select($selects);

        $sortOptions = request('sortOptions');
        if (is_array($sortOptions)) {
            foreach ($sortOptions as $option) {
                $column = $option['column'];
                $order = $option['order'];
                if (in_array($column, array_keys($this->primary_columns))) {
                    $q->orderBy($column, $order);
                }
            }
        } else {
            // Default sorting
            $q->orderBy('datetime', 'asc');
        }

        return $q->get();
    }
}
