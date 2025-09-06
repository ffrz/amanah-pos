<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Customer::where('active', '=', true);
        if ($query = $request->query('q', '')) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('username', 'like', '%' . $query . '%')
                ->orWhere('phone', 'like', '%' . $query . '%');
        }
        $customers = $q->get();
        return JsonResponseHelper::success($customers);
    }
}
