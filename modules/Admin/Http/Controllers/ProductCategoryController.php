<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return inertia('product-category/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = ProductCategory::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = ProductCategory::findOrFail($id);
        $item->id = null;
        return inertia('product-category/Editor', [
            'data' => $item
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? ProductCategory::findOrFail($id) : new ProductCategory();
        return inertia('product-category/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = $request->id ? ProductCategory::findOrFail($request->id) : new ProductCategory();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('product_categories', 'name')->ignore($item->id),
            ],
            'description' => 'nullable|max:1000',
        ]);

        $validated['description'] = $validated['description'] ? $validated['description'] : '';

        $item->fill($validated);
        $item->save();

        return redirect()
            ->route('admin.product-category.index')
            ->with('success', "Kategori $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = ProductCategory::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.product-category-deleted', ['name' => $item->name])
        ]);
    }
}
