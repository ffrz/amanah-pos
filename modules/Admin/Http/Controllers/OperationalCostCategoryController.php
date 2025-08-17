<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OperationalCostCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperationalCostCategoryController extends Controller
{
    public function index()
    {
        return inertia('operational-cost-category/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = OperationalCostCategory::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = OperationalCostCategory::findOrFail($id);
        $item->id = null;
        return inertia('operational-cost-category/Editor', [
            'data' => $item
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? OperationalCostCategory::findOrFail($id) : new OperationalCostCategory();
        return inertia('operational-cost-category/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = $request->id ? OperationalCostCategory::findOrFail($request->id) : new OperationalCostCategory();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('operational_cost_categories', 'name')->ignore($item->id),
            ],
            'description' => 'nullable|max:1000',
        ]);

        $item->fill([
            'name' => $validated['name'],
            'description' => $data['description'] ?? '',
        ]);

        $item->save();

        $messageKey = $request->id ? 'operational-cost-category-updated' : 'operational-cost-category-created';

        return redirect(route('admin.operational-cost-category.index'))
            ->with('success', __("messages.$messageKey", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = OperationalCostCategory::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.operational-cost-category-deleted', ['name' => $item->name])
        ]);
    }
}
