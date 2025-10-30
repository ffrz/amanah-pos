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

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerReportController extends BaseController
{
    public function index(Request $request)
    {
        return inertia('reports/customer/Index', []);
    }

    public function list(Request $request)
    {
        $q = Customer::query();

        $filter = (array)$request->get('filter', []);
        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('active', $filter['status'] == 'active');
        }

        if (!empty($filter['default_price_type']) && $filter['default_price_type'] != 'all') {
            $q->where('default_price_type', $filter['default_price_type']);
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', $filter['type']);
        }

        $sortOptions = $request->get('sortOptions');

        $q->orderBy($sortOptions[0]['column'], $sortOptions[0]['order']);

        $items = $q->select($request->get('columns'))->get();

        $orientation = $this->getPageOrientation($request);

        $title = 'Laporan Daftar Pelanggan';

        return $this->generatePdfReport(
            'modules.admin.pages.reports.customer.list',
            $orientation,
            compact(
                'items',
                'title',
                'orientation'
            ),
            $request->get('format')
        );
    }
}
