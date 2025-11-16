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

use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Services\CommonDataService;

// Asumsi BaseController menyediakan generateIndexResponse dan generateReport
class FinanceTransactionReportController extends BaseController
{
    protected string $default_title = 'Laporan Daftar Transaksi Keuangan';

    protected array $templates = [
        'finance_transaction_list' =>
        [
            'value' => 'finance_transaction_list',
            'label' => 'Daftar Transaksi Keuangan',
        ],
    ];

    protected $primary_columns = [];

    protected $optional_columns = [];

    protected $initial_columns = [];

    protected $initial_sorts = [];

    protected $initial_filter = [
        "accounts"   => [],
        "categories" => [],
        "tags"       => [],
        "type"       => "all",
    ];

    protected $sorts_editable = false;
    protected $columns_editable = false;
    protected $page_orientation_editable = false;

    public function index()
    {
        $service = app(CommonDataService::class);

        return $this->generateIndexResponse('reports/finance-transaction/Index', [
            'accounts' => $service->getFinanceAccounts(),
            'categories' => $service->getFinanceTransactionCategories(),
            'tags' => $service->getFinanceTransactionTags(),
            'types' => FinanceTransaction::Types,
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),

            // Validasi Filter Transaksi
            'filter.start_date' => 'nullable|date',
            'filter.end_date'  => 'nullable|date',
            'filter.accounts'  => 'nullable|array',
            'filter.accounts.*'  => 'string|integer',
            'filter.categories'  => 'nullable|array',
            'filter.categories.*'  => 'string',
            'filter.tags'    => 'nullable|array',
            'filter.tags.*'  => 'string|integer',
            'filter.type'    => 'nullable|string',
        ]);

        // Query Builder untuk FinanceTransaction
        $q = FinanceTransaction::query()->with(['account', 'category', 'tags']);

        $filter = (array)$data['filter'];

        // 1. Filter Rentang Waktu
        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        // 2. Filter Akun (Multiple/Array)
        if (!empty($filter['accounts'])) {
            $accountIds = is_array($filter['accounts']) ? $filter['accounts'] : [$filter['accounts']];
            // Filter hanya jika ID yang diberikan valid
            if (!empty($accountIds) && count(array_filter($accountIds)) > 0) {
                $q->whereIn('account_id', $accountIds);
            }
        }

        // 3. Filter Kategori
        if (!empty($filter['categories'])) {
            $ids = is_array($filter['categories']) ? $filter['categories'] : [$filter['categories']];
            // Filter hanya jika ID yang diberikan valid
            if (!empty($ids) && count(array_filter($ids)) > 0) {
                $q->whereIn('category_id', $ids);
            }
        }

        // 4. Filter Tags (Multiple/Array)
        if (!empty($filter['tags']) && is_array($filter['tags'])) {
            $tagIds = array_map('intval', $filter['tags']);

            // Hanya filter jika ada tag ID yang valid
            if (!empty(array_filter($tagIds))) {
                $q->whereHas('tags', function (Builder $query) use ($tagIds) {
                    $query->whereIn('finance_transaction_tag_id', $tagIds);
                });
            }
        }

        // 5. Filter Jenis Transaksi (Jika Anda mau menambahkannya)
        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', $filter['type']);
        }

        // Ambil data dan generate laporan
        return $this->generateReport(
            'modules.admin.pages.reports.finance-transaction.list', // Pastikan path view ini benar
            $data,
            $q
        );
    }

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns, $data)
    {
        $q->orderBy('datetime', 'asc');

        return $q->select(["*"])->get();
    }
}
