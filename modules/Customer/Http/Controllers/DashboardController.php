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

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = Customer::findOrFail(Auth::guard('customer')->user()->id);
        $month = $request->get('month', 'this_month');
        if (!in_array($month, ['this_month', 'prev_month', 'prev_2month', 'prev_3month'])) {
            $month = 'this_month';
        }

        // Tentukan rentang tanggal berdasarkan bulan yang dipilih
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

        $total_income = CustomerWalletTransaction::where('customer_id', '=', $customer->id)
            ->where('amount', '>', 0)
            ->whereBetween('datetime', [$startDate, $endDate])
            ->sum('amount');

        // Ambil total expense dalam bulan yang dipilih
        // Menggunakan abs() untuk mengubah nilai negatif menjadi positif untuk total expense
        $total_expense = abs(
            CustomerWalletTransaction::where('customer_id', '=', $customer->id)
                ->where('amount', '<', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
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

        return inertia('dashboard/Index', [
            'data' => [
                'actual_balance' => $customer->balance,
                'total_income' => $total_income,
                'total_expense' => $total_expense,
                'recent_wallet_transactions' => $recent_wallet_transactions,
                'recent_purchase_orders' => $recent_purchase_orders,
            ]
        ]);
    }
}
