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

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierReportController extends BaseController
{
    protected $primary_columns = [
        'code' => 'Kode',
        'name' => 'Nama',
    ];

    protected $optional_columns = [
        "phone_1" => "No Telepon",
        "phone_2" => "No Telepon 2",
        "phone_3" => "No Telepon 3",
        "balance" => "Saldo Utang / Piutang",
        "address" => "Alamat",
        "return_address" => "Alamat Retur",
        "active" => "Status",
    ];

    protected $initial_columns = [
        'code',
        'name',
        'phone_1',
        'address'
    ];

    public function index()
    {
        return inertia('reports/supplier/Index', [
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
            ...$this->getDefaultValidationRules()
        ]);

        $q = Supplier::query();

        if (!empty($data['filter']['status']) && $data['filter']['status'] != 'all') {
            $q->where('active', $data['filter']['status'] == 'active');
        }

        return $this->generatePdfReport(
            'modules.admin.pages.reports.supplier.list',
            [
                'title' => 'Laporan Daftar Supplier',
                'items' => $this->processQuery($q, $data['columns']),
                'filter' => $data['filter'],
                'columns' => $data['columns'],
            ]
        );
    }
}
