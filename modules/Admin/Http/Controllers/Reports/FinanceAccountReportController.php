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
use App\Models\FinanceAccount;
use Illuminate\Http\Request;

class FinanceAccountReportController extends BaseController
{
    protected string $default_title = 'Laporan Daftar Akun Keuangan';

    protected array $templates = [
        'finance_account_list' =>
        [
            'value' => 'finance_account_list',
            'label' => 'Daftar Akun Keuangan',
            'columns' => ['name', 'type', 'balance'],
        ],

    ];

    protected $primary_columns = [
        'name' => 'Nama Akun',
        'type' => 'Jenis',
        'balance' => 'Saldo',
    ];

    protected $optional_columns = [];

    protected $initial_columns = [
        'name',
        'type',
        'balance',
    ];

    protected $initial_sorts = [
        [
            'column' => 'name',
            'order'  => 'asc',
        ]
    ];

    protected $initial_filter = [
        "status" => "active",
        "type"   => "all",
    ];

    protected $page_orientation_editable = false;

    protected $sorts_editable = false;

    protected $columns_editable = false;

    public function index()
    {
        return $this->generateIndexResponse('reports/finance-account/Index');
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.type' => 'nullable|string',
        ]);

        $q = FinanceAccount::query();

        $filter = (array)$data['filter'];
        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('active', $filter['status'] == 'active');
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', $filter['type']);
        }

        return $this->generateReport(
            'modules.admin.pages.reports.finance-account.list',
            $data,
            $q
        );
    }
}
