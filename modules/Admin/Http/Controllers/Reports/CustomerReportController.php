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
    protected $primary_columns = [
        'code' => 'Kode',
        'name' => 'Nama',
    ];

    protected $optional_columns = [
        'phone'              => 'No Telepon',
        'address'            => 'Alamat',
        'balance'            => 'Saldo Utang / Piutang',
        'wallet_balance'     => 'Saldo Deposit',
        'active'             => 'Aktif / Nonaktif',
        'type'               => 'Jenis Akun',
        'default_price_type' => 'Level Harga',
    ];

    protected $initial_columns = [
        'code',
        'name',
        'phone',
        'address'
    ];

    public function index()
    {
        return inertia('reports/customer/Index', [
            'primary_columns' => $this->primary_columns,
            'optional_columns' => $this->optional_columns,
            'initial_columns' => $this->initial_columns,
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'filter' => 'nullable|array',
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.type' => 'nullable|string',
            'filter.default_price_type' => 'nullable|string',
            ...$this->getDefaultValidationRules()
        ]);

        $q = Customer::query();

        $filter = (array)$data['filter'];
        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('active', $filter['status'] == 'active');
        }

        if (!empty($filter['default_price_type']) && $filter['default_price_type'] != 'all') {
            $q->where('default_price_type', $filter['default_price_type']);
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', $filter['type']);
        }

        return $this->generatePdfReport(
            'modules.admin.pages.reports.customer.list',
            [
                'title' => 'Laporan Daftar Pelanggan',
                'items' => $this->processQuery($q, $data['columns']),
                'filter' => $data['filter'],
                'columns' => $data['columns'],
            ]
        );
    }
}
