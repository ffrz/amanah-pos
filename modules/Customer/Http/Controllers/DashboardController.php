<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = Customer::findOrFail(Auth::guard('customer')->user()->id);
        $month = $request->get('month', 'this_month');
        if (!in_array($month, ['this_month', 'prev_month', 'prev_2month', 'prev_3month'])) {
            $month = 'this_month';
        }

        // Panggil metode privat untuk mendapatkan rentang tanggal
        $dateRange = $this->getMonthlyDateRange($month);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];

        $total_income = CustomerWalletTransaction::where('customer_id', '=', $customer->id)
            ->where('amount', '>', 0)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->sum('amount');

        $total_expense = abs(
            CustomerWalletTransaction::where('customer_id', '=', $customer->id)
                ->where('amount', '<', 0)
                ->whereBetween('datetime', [$startDate, $endDate])
                ->sum('amount')
        );

        $recent_wallet_transactions = CustomerWalletTransaction::where('customer_id', '=', $customer->id)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->orderBy('id', 'desc')
            ->get();

        $recent_purchase_orders = SalesOrder::where('customer_id', '=', $customer->id)
            ->where('status', '=', SalesOrder::Status_Closed)
            ->orderBy('id', 'desc')
            ->get();

        // Panggil metode privat untuk mendapatkan data chart
        $monthlyChartData = $this->getMonthlyTransactionsChartData(
            $customer->id,
            $startDate,
            $endDate
        );

        return inertia('dashboard/Index', [
            'data' => [
                'actual_balance' => $customer->balance,
                'total_income' => $total_income,
                'total_expense' => $total_expense,
                'recent_wallet_transactions' => $recent_wallet_transactions,
                'recent_purchase_orders' => $recent_purchase_orders,
            ],
            'monthlyChartData' => $monthlyChartData
        ]);
    }

    /**
     * Menentukan rentang tanggal (start dan end) berdasarkan string bulan.
     * Metode ini bersifat privat agar hanya bisa dipanggil di dalam kelas ini.
     *
     * @param string $month
     * @return array
     */
    private function getMonthlyDateRange(string $month): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth();
        $endDate = $now->copy()->endOfMonth();

        if ($month === 'prev_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
        } elseif ($month === 'prev_2month') {
            $startDate = $now->copy()->subMonths(2)->startOfMonth();
            $endDate = $now->copy()->subMonths(2)->endOfMonth();
        } elseif ($month === 'prev_3month') {
            $startDate = $now->copy()->subMonths(3)->startOfMonth();
            $endDate = $now->copy()->subMonths(3)->endOfMonth();
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    /**
     * Mengambil dan memformat data transaksi harian untuk chart bulanan.
     * Metode ini bersifat privat agar hanya bisa dipanggil di dalam kelas ini.
     *
     * @param int $customerId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getMonthlyTransactionsChartData(int $customerId, Carbon $startDate, Carbon $endDate): array
    {
        $transactions = CustomerWalletTransaction::where('customer_id', $customerId)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(datetime) as date'),
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) as total_expense')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $daysInMonth = $startDate->daysInMonth;
        $monthlyIncomeData = array_fill(0, $daysInMonth, 0);
        $monthlyExpenseData = array_fill(0, $daysInMonth, 0);
        $monthlyLabels = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $monthlyLabels[] = $i;
        }

        foreach ($transactions as $transaction) {
            $day = Carbon::parse($transaction->date)->day;
            $index = $day - 1;
            $monthlyIncomeData[$index] = $transaction->total_income;
            $monthlyExpenseData[$index] = abs($transaction->total_expense);
        }

        return [
            'labels' => $monthlyLabels,
            'income' => $monthlyIncomeData,
            'expense' => $monthlyExpenseData,
        ];
    }
}
