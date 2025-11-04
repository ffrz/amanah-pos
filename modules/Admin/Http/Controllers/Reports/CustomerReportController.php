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
    protected string $default_title = 'Laporan Daftar Pelanggan';

    protected array $templates = [
        'customer_list' =>
        [
            'value' => 'customer_list',
            'label' => 'Daftar Pelanggan',
            'columns' => ['code', 'name', 'phone', 'address'],
        ],
        'customer_actual_wallet_balance' =>
        [
            'value' => 'customer_actual_wallet_balance',
            'label' => 'Saldo Deposit Pelanggan Aktual',
            'columns' => ['code', 'name', 'wallet_balance', 'phone', 'address'],
        ],
        'customer_actual_balance' =>
        [
            'value' => 'customer_actual_balance',
            'label' => 'Saldo Utang/Piutang Pelanggan Aktual',
            'columns' => ['code', 'name', 'balance', 'phone', 'address'],
        ],
    ];

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

    protected $initial_sorts = [
        [
            'column' => 'code',
            'order'  => 'asc',
        ]
    ];

    protected $initial_filter = [
        "status" => "active",
        "type"   => "all",
        "default_price_type" => "all",
    ];

    protected $page_orientation_editable = true;

    protected $sorts_editable = true;

    protected $columns_editable = true;

    public function index()
    {
        return $this->generateIndexResponse('reports/customer/Index');
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.type' => 'nullable|string',
            'filter.default_price_type' => 'nullable|string',
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

        if (!empty($data['template'])) {
            if ($data['template'] == 'customer_actual_wallet_balance') {
                $q->where('wallet_balance', '<>', 0);
            }
            if ($data['template'] == 'customer_actual_balance') {
                $q->where('balance', '<>', 0);
            }
        }

        return $this->generateReport(
            'modules.admin.pages.reports.customer.list',
            $data,
            $q
        );
    }
}
