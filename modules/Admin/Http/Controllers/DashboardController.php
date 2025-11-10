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

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $dates = getDateRangeByPeriod($period);
        $start_date = $dates['start_date']->toDateTimeString();
        $end_date = $dates['end_date']->toDateTimeString();
        $aggregationType = $period == 'today' || $period == 'yesterday' ?
            false : ((str_contains($period, 'year')) ? 'monthly' : 'daily');
        $revenueByCategory = SalesOrder::aggregateRevenueByCategory($start_date, $end_date);
        $topCustomerRevenue = SalesOrder::getTopCustomersByRevenue($start_date, $end_date, 5);
        $topCustomerWallet = Customer::getTopCustomersByWalletBalance(5);
        return inertia('dashboard/Index', [
            'data' => [
                'total_finance_balance' => FinanceAccount::totalActiveBalance(),
                'total_finance_income' => FinanceTransaction::totalIncome($start_date, $end_date),
                'total_finance_expense' => FinanceTransaction::totalExpense($start_date, $end_date),
                'total_product_item' => Product::totalItem(),
                'total_product_cost' => Product::totalCost(),
                'total_product_price' => Product::totalPrice(),
                'total_active_customer' => Customer::activeCustomerCount(),
                'total_active_supplier' => Supplier::activeSupplierCount(),
                'total_finance_account_balance' => FinanceAccount::totalActiveBalance(),
                'total_customer_wallet_balance' => Customer::totalActiveWalletBalance(),
                'total_customer_balance' => Customer::totalActiveBalance(),
                'total_supplier_wallet_balance' => 0,
                'total_supplier_balance' => Supplier::totalActiveBalance(),
                'total_sales' => SalesOrder::sumClosedTotalByPeriod($start_date, $end_date),
                'total_sales_count' => SalesOrder::countClosedByPeriod($start_date, $end_date),
                'total_sales_profit' => SalesOrder::sumTotalProfitByPeriod($start_date, $end_date),
                'chart_data_1' => $aggregationType ? $this->formatSalesChartData(
                    SalesOrder::getSalesDataAggregatedByPeriod($start_date, $end_date, $aggregationType),
                    $dates['start_date'],
                    $dates['end_date'],
                    $aggregationType
                ) : [],
                'revenue_by_category' => $this->formatPieChartData(
                    $revenueByCategory,
                    'total_revenue',
                    'category_name'
                ),
                'top_customers_revenue' => $topCustomerRevenue,
                'top_customers_wallet' => $topCustomerWallet
            ]
        ]);
    }

    /**
     * Memformat Collection agregasi kategori menjadi format ECharts/Pie Chart.
     * @param string $valueKey Kunci kolom nilai (e.g., 'total_revenue', 'total_qty_sold').
     * @param string $nameKey Kunci kolom nama (e.g., 'category_name').
     * @return array Berisi array of objects untuk ECharts.
     */
    protected function formatPieChartData(\Illuminate\Support\Collection $data, string $valueKey, string $nameKey): array
    {
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'name' => $item->{$nameKey},
                'value' => (float)$item->{$valueKey},
            ];
        }
        return $formattedData;
    }

    public function formatSalesChartData(\Illuminate\Support\Collection $data, Carbon $startDate, Carbon $endDate, string $aggregation): array
    {
        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        // Konversi Collection ke Map (period_label => total_sales) untuk pencarian cepat
        $salesMap = $data->pluck('total_sales', 'period_label')->toArray();

        // Clone tanggal awal
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $periodLabel = '';
            $labelFormat = '';

            if ($aggregation === 'monthly') {
                // Label periode untuk perbandingan (YYYY-MM) dan Label chart (Nama Bulan)
                $periodLabel = $currentDate->format('Y-m');
                $labelFormat = $currentDate->shortEnglishMonth; // Misal: Jan, Feb, Mar
            } else { // daily
                // Label periode untuk perbandingan (YYYY-MM-DD) dan Label chart (Nama Hari/Tanggal)
                $periodLabel = $currentDate->toDateString();
                $labelFormat = $currentDate->format('D, d'); // Misal: Mon, 01
            }

            // Ambil data penjualan, default 0 jika tidak ada di map
            $salesValue = $salesMap[$periodLabel] ?? 0;

            $chartData['labels'][] = $labelFormat;
            $chartData['data'][] = (float)$salesValue;

            // Pindah ke periode berikutnya
            if ($aggregation === 'monthly') {
                $currentDate->addMonth();
            } else {
                $currentDate->addDay();
            }

            // Safety break: jika aggregasi bulanan, jangan sampai melebihi end_date
            if ($aggregation === 'monthly' && $currentDate->gt($endDate)) {
                break;
            }
        }

        return $chartData;
    }

    /**
     * This method exists here for testing purpose only.
     */
    public function test()
    {
        return inertia('dashboard/Test');
    }
}
