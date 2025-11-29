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

use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use Illuminate\Http\Request;
use Modules\Admin\Services\CommonDataService;

class OperationalCostDetailReportController extends BaseController
{
    protected string $default_title = 'Laporan Rincian Biaya Operasional';

    protected array $templates = [
        'operational_cost_list' =>
        [
            'value' => 'operational_cost_list',
            'label' => 'Daftar Rincian Biaya Operasional',
        ],
    ];

    protected $initial_filter = [
        "accounts"   => [],
        "categories" => [],
        // "tags" dan "type" dihapus karena tidak ada di model OperationalCost
    ];

    protected $sorts_editable = false;
    protected $columns_editable = false;
    protected $page_orientation_editable = false;

    public function index()
    {
        $service = app(CommonDataService::class);

        // Mengambil data untuk dropdown filter di Vue
        return $this->generateIndexResponse('reports/operational-cost/Index', [
            'accounts' => $service->getFinanceAccounts(),
            // Mengambil kategori khusus Operational Cost
            'categories' => OperationalCostCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),

            // Validasi Filter
            'filter.start_date' => 'nullable|date',
            'filter.end_date'   => 'nullable|date',
            'filter.accounts'   => 'nullable|array',
            'filter.accounts.*' => 'integer', // ID Akun biasanya integer
            'filter.categories' => 'nullable|array',
            'filter.categories.*' => 'string', // Bisa string "none" atau ID integer
        ]);

        // Query Builder untuk OperationalCost
        // Relasi disesuaikan dengan method di Model: financeAccount dan category
        $q = OperationalCost::query()->with(['financeAccount', 'category']);

        $filter = (array)$data['filter'];

        // 1. Filter Rentang Waktu (Kolom: date)
        if (!empty($filter['start_date'])) {
            $q->where('date', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $q->where('date', '<=', $filter['end_date']);
        }

        // 2. Filter Akun (Kolom: finance_account_id)
        if (!empty($filter['accounts'])) {
            $accountIds = is_array($filter['accounts']) ? $filter['accounts'] : [$filter['accounts']];

            if (!empty($accountIds) && count(array_filter($accountIds)) > 0) {
                $q->whereIn('finance_account_id', $accountIds);
            }
        }

        // 3. Filter Kategori (Kolom: category_id)
        if (!empty($filter['categories'])) {
            $catFilters = is_array($filter['categories']) ? $filter['categories'] : [$filter['categories']];

            // Pisahkan logic jika ada filter 'none' (Tanpa Kategori) dan ID numeric
            $ids = array_filter($catFilters, 'is_numeric');
            $hasNone = in_array('none', $catFilters);

            $q->where(function ($query) use ($ids, $hasNone) {
                if (!empty($ids)) {
                    $query->whereIn('category_id', $ids);
                }
                if ($hasNone) {
                    $query->orWhereNull('category_id');
                }
            });
        }

        // Ambil data dan generate laporan
        return $this->generateReport(
            'modules.admin.pages.reports.operational-cost-detail.list', // Path view baru
            $data,
            $q
        );
    }

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns, $data)
    {
        // Urutkan berdasarkan tanggal, lalu created_at
        $q->orderBy('date', 'asc')->orderBy('created_at', 'asc');

        return $q->select(["*"])->get();
    }
}
