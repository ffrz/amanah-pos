<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = Customer::findOrFail(Auth::guard('customer')->user()->id);
        return inertia('dashboard/Index', [
            'data' => [
                'actual_balance' => $customer->balance
            ]
        ]);
    }
}
