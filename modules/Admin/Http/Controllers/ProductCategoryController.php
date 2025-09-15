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

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Http\Request;
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

        $validated['description'] = $validated['description'] ?? '';

        $item->fill($validated);
        $item->save();

        return JsonResponseHelper::success($item, "Kategori $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = ProductCategory::findOrFail($id);
        try {
            $item->delete();
        } catch (Exception $ex) {
            return JsonResponseHelper::error('Gagal menghapus kategori', 500, $ex);
        }


        return JsonResponseHelper::success($item, "Kategori $item->name telah dihapus.");
    }
}
