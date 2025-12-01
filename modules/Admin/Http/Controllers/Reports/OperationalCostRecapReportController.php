<?php

namespace Modules\Admin\Http\Controllers\Reports;

use App\Models\OperationalCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\CommonDataService;

class OperationalCostRecapReportController extends BaseController
{
    protected string $default_title = 'Rekapitulasi Biaya Operasional Per Kategori';

    protected array $templates = [
        'operational_cost_recap_category' => [
            'value' => 'operational_cost_recap_category',
            'label' => 'Rekap Per Kategori',
        ],
    ];

    protected $initial_filter = [
        "accounts" => [],
    ];

    // Kita matikan fitur edit kolom karena ini laporan rekap (bukan list)
    protected $sorts_editable = false;
    protected $columns_editable = false;
    protected $page_orientation_editable = false;

    public function index()
    {
        $service = app(CommonDataService::class);
        // Menggunakan generateIndexResponse seperti controller sebelumnya
        return $this->generateIndexResponse('reports/operational-cost-recap/Index', [
            'accounts' => $service->getFinanceAccounts(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            'filter.start_date' => 'required|date',
            'filter.end_date'   => 'required|date',
            'filter.accounts'   => 'nullable|array',
        ]);

        $filter = (array)$data['filter'];

        // 1. QUERY BUILDER: Siapkan query dasar dengan GROUP BY
        $q = OperationalCost::query()
            ->select('category_id', DB::raw('count(*) as total_trx'), DB::raw('sum(amount) as total_amount'))
            ->with(['category']) // Load relasi untuk nama kategori
            ->groupBy('category_id');

        // 2. APPLY FILTERS

        // Filter Tanggal
        $q->where('date', '>=', $filter['start_date']);
        $q->where('date', '<=', $filter['end_date']);

        // Filter Akun (Opsional)
        if (!empty($filter['accounts'])) {
            $accountIds = is_array($filter['accounts']) ? $filter['accounts'] : [$filter['accounts']];
            if (!empty(array_filter($accountIds))) {
                $q->whereIn('finance_account_id', $accountIds);
            }
        }

        // 3. GENERATE REPORT
        // Menggunakan method bawaan BaseController
        return $this->generateReport(
            'modules.admin.pages.reports.operational-cost-recap.list', // Pastikan view ini ada
            $data,
            $q
        );
    }

    /**
     * Override processQuery untuk menangani eksekusi data rekapitulasi.
     * Method ini dipanggil otomatis di dalam $this->generateReport()
     */
    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns, $data)
    {
        // Urutkan dari pengeluaran terbesar
        $q->orderBy('total_amount', 'desc');

        // Eksekusi query
        // Tidak perlu transform manual di sini, kita handle null category di View saja
        return $q->get();
    }
}
