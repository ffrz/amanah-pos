<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WashService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WashServiceController extends Controller
{
    public function index()
    {
        return inertia('admin/wash-service/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/wash-service/Detail', [
            'data' => WashService::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = WashService::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = WashService::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/wash-service/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? WashService::findOrFail($id) : new WashService(['active' => true]);
        return inertia('admin/wash-service/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'max:40',
                Rule::unique('wash_services', 'name')->ignore($request->id), // agar saat update tidak dianggap duplikat sendiri
            ],
            'description' => 'required|max:100',
            'duration' => 'numeric|min:0|max:360',
            'price' => 'numeric|min:0|max:999999',
        ];

        $item = null;
        $message = '';
        $fields = ['name', 'description', 'duration', 'price', 'active'];

        $request->validate($rules);

        if (!$request->id) {
            $item = new WashService();
            $message = 'wash-service-created';
        } else {
            $item = WashService::findOrFail($request->post('id', 0));
            $message = 'wash-service-updated';
        }

        $item->fill($request->only($fields));
        $item->save();

        return redirect(route('admin.wash-service.index'))->with('success', __("messages.$message", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = WashService::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.wash-service-deleted', ['name' => $item->name])
        ]);
    }
}
