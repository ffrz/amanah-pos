<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    //
    public function index()
    {
        return inertia('admin/service-order/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = ServiceOrder::query();
        $q->orderBy($orderBy, $orderType);

        if (!empty($filter['order_status']) && $filter['order_status'] != 'all') {
            $q->where('order_status', '=', $filter['order_status']);
        }

        if (!empty($filter['service_status']) && $filter['service_status'] != 'all') {
            $q->where('service_status', '=', $filter['service_status']);
        }

        if (!empty($filter['payment_status']) && $filter['payment_status'] != 'all') {
            $q->where('payment_status', '=', $filter['payment_status']);
        }

        if (!empty($filter['repair_status']) && $filter['repair_status'] != 'all') {
            $q->where('repair_status', '=', $filter['repair_status']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $q->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%');
                $q->orWhere('customer_phone', 'like', '%' . $search . '%');
                $q->orWhere('customer_address', 'like', '%' . $search . '%');
                $q->orWhere('device', 'like', '%' . $search . '%');
            });
        }

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function detail($id = 0)
    {
        $item = ServiceOrder::with([
            'createdBy:id,username,name',
            'updatedBy:id,username,name',
            'closedBy:id,username,name',
            'technician:id,name'
        ])->findOrFail($id);

        return inertia('admin/service-order/Detail', [
            'data' => $item
        ]);
    }

    public function duplicate($id)
    {
        $item = $this->_findOder($id);
        $item->id = null;
        $item->created_datetime = null;
        $item->created_by_uid = null;
        $item->updated_datetime = null;
        $item->updated_by_uid = null;
        $item->closed_datetime = null;
        $item->closed_by_uid = null;
        $item->order_status = ServiceOrder::OrderStatus_Open;
        $item->service_status = ServiceOrder::ServiceStatus_Received;
        $item->repair_status = ServiceOrder::RepairStatus_NotFinished;
        $item->payment_status = ServiceOrder::PaymentStatus_Unpaid;
        $item->received_datetime = date('Y-m-d H:i:s');
        $item->checked_datetime = null;
        $item->worked_datetime = null;
        $item->completed_datetime = null;
        $item->picked_datetime = null;

        return $this->_renderEditor($item);
    }

    public function editor($id = 0)
    {
        $item = $this->_findOder($id);
        return $this->_renderEditor($item);
    }

    private function _findOder($id)
    {
        return $id ? ServiceOrder::with([
            'createdBy:id,username,name',
            'updatedBy:id,username,name',
            'closedBy:id,username,name',
        ])->findOrFail($id) : new ServiceOrder([
            'received_datetime' => date('Y-m-d H:i:s'),
            'order_status' => ServiceOrder::OrderStatus_Open,
            'service_status' => ServiceOrder::ServiceStatus_Received,
            'payment_status' => ServiceOrder::PaymentStatus_Unpaid,
            'repair_status' => ServiceOrder::RepairStatus_NotFinished,
        ]);
    }

    private function _renderEditor($item)
    {
        $customers = Customer::get(['id', 'name', 'phone', 'address']);

        $washers = User::where('role', User::Role_Washer)
            ->get(['id', 'name']);

        return inertia('admin/service-order/Editor', [
            'data' => $item,
            'technicians' => $washers,
            'customers' => $customers,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'customer_name' => 'required|max:255',
            'customer_phone' => 'required|max:100',
            'address' => 'nullable|max:1000',

            'device' => 'required',
            'equipments' => 'required',
        ];
        $item = null;
        $fields = [
            'order_status',
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_address',
            'device_type',
            'device',
            'equipments',
            'device_sn',
            'problems',
            'actions',
            'service_status',
            'repair_status',
            'technician_id',
            'received_datetime',
            'checked_datetime',
            'worked_datetime',
            'completed_datetime',
            'picked_datetime',
            'payment_status',
            'down_payment',
            'estimated_cost',
            'total_cost',
            'warranty_start_date',
            'warranty_day_count',
            'notes'
        ];

        $request->validate($rules);

        $data = $request->only($fields);
        $data['device_sn'] = $data['device_sn'] ?? '';
        $data['notes'] = $data['notes'] ?? '';
        $data['estimated_cost'] = $data['estimated_cost'] ?? 0;
        $data['down_payment'] = $data['down_payment'] ?? 0;
        $data['total_cost'] = $data['total_cost'] ?? 0;
        $data['warranty_start_date'] = $data['warranty_start_date'] ?? null;
        $data['warranty_day_count'] = $data['warranty_day_count'] ?? 0;

        if (!$request->id) {
            $item = new ServiceOrder();
            $item->created_by_uid = Auth::user()->id;
            $item->created_datetime = date('Y-M-d H:i:s');
        } else {
            $item = ServiceOrder::findOrFail($request->post('id', 0));
            $item->updated_by_uid = Auth::user()->id;
            $item->updated_datetime = date('Y-M-d H:i:s');
        }

        if ($data['order_status'] == 'closed') {
            $item->closed_by_uid = Auth::user()->id;
            $item->closed_datetime = date('Y-M-d H:i:s');
        } else {
            $item->closed_by_uid = null;
            $item->closed_datetime = null;
        }

        DB::transaction(function () use ($data, $item, $request) {
            if (!$data['customer_id']) {
                $customer = Customer::create([
                    'name' => $request->input('customer_name'),
                    'phone' => $request->input('customer_phone'),
                    'address' => $request->input('customer_address'),
                ]);
                $data['customer_id'] = $customer->id;
            }

            $item->fill($data);
            $item->save();
        });

        return redirect(route('admin.service-order.edit', ['id' => $item->id]))->with('success', __('messages.service-order-saved', ['id' => $item->id]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = ServiceOrder::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.service-order-deleted', ['id' => $item->id])
        ]);
    }
}
