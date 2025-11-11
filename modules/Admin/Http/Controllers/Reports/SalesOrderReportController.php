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

class SalesOrderReportController extends BaseController
{
    protected array $views = [
        'sales_order_recap' => 'modules.admin.pages.reports.sales-order.recap',
        'sales_order_detail' => 'modules.admin.pages.reports.sales-order.detail',
        'profit_recap' => 'modules.admin.pages.reports.sales-order.profit',
    ];

    protected array $templates = [
        'sales_order_recap' => [
            'value' => 'sales_order_recap',
            'label' => 'Laporan Rekapitulasi Penjualan',
        ],
        'sales_order_detail' => [
            'value' => 'sales_order_detail',
            'label' => 'Laporan Rincian Penjualan',
        ],
        'profit_recap' => [
            'value' => 'profit_recap',
            'label' => 'Laporan Rekapitulasi Laba',
        ],
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
            'filter.start_date' => 'required|date',
            'filter.end_date' => 'required|date',
            'filter.customer_ids' => 'nullable|array',
            'filter.customer_ids.*' => 'integer',
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

        $q->where('status', \App\Models\SalesOrder::Status_Closed);

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        if (isset($filter['customer_ids']) && is_array($filter['customer_ids'])) {
            $customer_ids = array_filter($filter['customer_ids']);
            if (!empty($customer_ids)) {
                $q->whereIn('customer_id', $customer_ids);
            }
        }

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
            'sales_orders.total_discount',
            'sales_orders.total_tax',
        ];

        $selects[] = 'sales_orders.datetime';
        if ($data['template'] === 'profit_recap') {
            if (!in_array('total_cost', $selects)) {
                $selects[] = 'sales_orders.total_cost';
            }

            $selects[] = DB::raw('sales_orders.grand_total - sales_orders.total_cost as total_profit');
        } else if ($data['template'] === 'sales_order_detail') {
            $q->with([
                'details' => function ($r) {
                    $r->whereNull('return_id')
                        ->select(['order_id', 'product_name', 'product_barcode', 'quantity', 'product_uom', 'price', 'subtotal_price', 'notes']);
                }
            ]);
            $selects[] = DB::raw("COALESCE({$subqueryTotalItems}, 0) as total_item");
        } else {
            $selects[] = DB::raw("COALESCE({$subqueryTotalItems}, 0) as total_item");
        }

        $q->select($selects);

        $q->orderBy('datetime', 'asc');

        return $q->get();
    }
}
