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
    protected string $default_title = 'Laporan Daftar Supplier';

    protected array $templates = [
        'supplier_list' =>
        [
            'value' => 'supplier_list',
            'label' => 'Daftar Supplier',
            'columns' => ['code', 'name', 'phone_1', 'address'],
        ],
        // 'supplier_actual_wallet_balance' =>
        // [
        //     'value' => 'supplier_actual_wallet_balance',
        //     'label' => 'Saldo Deposit Pemasok Aktual',
        //     'columns' => ['code', 'name', 'wallet_balance', 'phone_1', 'address'],
        // ],
        'supplier_actual_balance' =>
        [
            'value' => 'supplier_actual_balance',
            'label' => 'Saldo Utang/Piutang Supplier Aktual',
            'columns' => ['code', 'name', 'balance', 'phone_1', 'address'],
        ],
    ];

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
        return $this->generateIndexResponse('reports/supplier/Index');
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            'filter.status' => 'nullable|string|in:all,active,inactive',
        ]);

        $q = Supplier::query();

        if (!empty($data['filter']['status']) && $data['filter']['status'] != 'all') {
            $q->where('active', $data['filter']['status'] == 'active');
        }

        if (!empty($data['template'])) {
            if ($data['template'] == 'supplier_actual_wallet_balance') {
                $q->where('wallet_balance', '<>', 0);
            }

            if ($data['template'] == 'supplier_actual_balance') {
                $q->where('balance', '<>', 0);
            }
        }

        return $this->generateReport(
            'modules.admin.pages.reports.supplier.list',
            $data,
            $q
        );
    }
}
