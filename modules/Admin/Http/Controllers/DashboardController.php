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
use App\Models\SalesOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $dates = getDateRangeByPeriod($period);
        $start_date = $dates['start_date']->toDateTimeString();
        $end_date = $dates['end_date']->toDateTimeString();
        // $days = [];
        // for ($i = 1; $i <= substr($end_date, 8, 2); $i++) {
        //     $days[(int)$i] = 0;
        // }

        // $sales_orders =
        //     SalesOrder::sumClosedOrderByPeriod($start_date, $end_date);
        // $monthly_closed_orders = $days;
        // foreach ($sales_orders as $order) {
        //     $monthly_closed_orders[(int)substr($order->order_date, 8, 2)] = $order->total_order;
        // }

        return inertia('dashboard/Index', [
            'data' => [
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
            ]
        ]);
    }

    /**
     * This method exists here for testing purpose only.
     */
    public function test()
    {
        return inertia('dashboard/Test');
    }
}
