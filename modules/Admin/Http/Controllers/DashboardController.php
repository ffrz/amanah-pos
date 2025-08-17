<?php

namespace Modules\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', 'this_month');
        $start_date = new Carbon(date('Y-m-01'));
        if ($month === 'prev_month') {
            $start_date = $start_date->copy()->subMonth()->startOfMonth();
        } else if ($month === 'prev_2month') {
            $start_date = $start_date->copy()->subMonth(2)->startOfMonth();
        } else if ($month === 'prev_3month') {
            $start_date = $start_date->copy()->subMonth(3)->startOfMonth();
        }
        $end_date = $start_date->copy()->endOfMonth();

        $days = [];
        for ($i = 1; $i <= substr($end_date, 8, 2); $i++) {
            $days[(int)$i] = 0;
        }

        $openedOrders = [];
        $monthly_opened_orders = $days;
        foreach ($openedOrders as $order) {
            $monthly_opened_orders[(int)substr($order->order_date, 8, 2)] = $order->total_order;
        }

        $successfullServices = [];
        $monthly_successfull_services = $days;
        foreach ($successfullServices as $order) {
            $monthly_successfull_services[(int)substr($order->order_date, 8, 2)] = $order->total_order;
        }

        $failedServices = [];
        $monthly_failed_services = $days;
        foreach ($failedServices as $order) {
            $monthly_failed_services[(int)substr($order->order_date, 8, 2)] = $order->total_order;
        }

        $closedOrders = []; //ServiceOrder::closedOrderByPeriod($start_date, $end_date);
        $monthly_closed_orders = $days;
        foreach ($closedOrders as $order) {
            $monthly_closed_orders[(int)substr($order->order_date, 8, 2)] = $order->total_order;
        }

        return inertia('admin/dashboard/Index', [
            'data' => [
                'active_customer_count' => Customer::activeCustomerCount(),
                'active_user_count' => User::activeUserCount(),
                'total_finance_account_balance' => FinanceAccount::totalActiveBalance(),
                'total_customer_balance' => Customer::totalActiveBalance(),
                'total_customer_debt' => Customer::totalActiveDebt(),
                'total_customer_credit' => Customer::totalActiveCredit(),

                'chart1_data' => [
                    'x_axis_label_data' => array_keys($monthly_opened_orders),
                    'data' => [
                        [
                            'label' => 'Diterima',
                            'data' => array_values($monthly_opened_orders),
                        ],
                        [
                            'label' => 'Sukses',
                            'data' => array_values($monthly_successfull_services),
                        ],
                        [
                            'label' => 'Gagal',
                            'data' => array_values($monthly_failed_services),
                        ]
                    ]
                ],
                'chart2_data' => [
                    'x_axis_label_data' => array_keys($monthly_opened_orders),
                    'data' => [
                        [
                            'label' => 'Closing',
                            'data' => array_values($monthly_closed_orders),
                        ],
                    ]
                ],
            ]
        ]);
    }

    /**
     * This method exists here for testing purpose only.
     */
    public function test()
    {
        return inertia('admin/dashboard/Test');
    }
}
