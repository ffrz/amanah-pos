<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockAdjustmentController extends Controller
{
    //
    public function index()
    {
        return inertia('stock-adjustment/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = StockAdjustment::with(['createdBy:id,username,name', 'updatedBy:id,username,name']);
        $q->orderBy($orderBy, $orderType);

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        if (!empty($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', '=', $filter['type']);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $q->where(function ($q) use ($search) {
                $q->where('notes', 'like', '%' . $search . '%');
            });
        }

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function detail($id = 0)
    {
        $item = StockAdjustment::with(['createdBy', 'updatedBy'])->findOrFail($id);

        return inertia('stock-adjustment/Detail', [
            'data' => $item,
            'details' => $item->details
        ]);
    }

    public function create(Request $request)
    {
        if ($request->method() == 'POST') {
            $product_ids = $request->post('product_ids', []);
            $products = Product::whereIn('id', $product_ids)->get()->keyBy('id');

            DB::beginTransaction();
            $item = new StockAdjustment([
                'datetime' => $request->post('datetime', date('Y-m-d H:i:s')),
                'status' => StockAdjustment::Status_Draft,
                'type' => $request->post('type', StockAdjustment::Type_StockCorrection),
                'notes' => $request->post('notes', ''),
                'total_cost' => 0,
                'total_price' => 0,
            ]);
            $item->save();

            foreach ($product_ids as $product_id) {
                $product = $products[$product_id];
                $detail = new StockAdjustmentDetail([
                    'parent_id' => $item->id,
                    'product_id' => $product_id,
                    'product_name' => $product->name,
                    'old_quantity' => $product->stock,
                    'new_quantity' => $product->stock,
                    'balance' => 0,
                    'uom' => $product->uom,
                    'cost' => $product->cost,
                    'price' => $product->price,
                ]);
                $detail->save();
            }

            DB::commit();

            return redirect(route('admin.stock-adjustment.editor', [
                'id' => $item->id
            ]))->with([
                'message' => __('messages.stock-adjustment-created', ['id' => $item->id])
            ]);
        }

        return inertia('stock-adjustment/Create', [
            'products' => Product::with(['category'])
                ->where('type', Product::Type_Stocked)
                ->where('active', 1)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'type', 'stock', 'uom', 'cost', 'price']),
        ]);
    }

    public function editor($id)
    {
        $item = StockAdjustment::findOrFail($id);

        $details = DB::table('stock_adjustment_details')
            ->join('products', 'stock_adjustment_details.product_id', '=', 'products.id')
            ->where('stock_adjustment_details.parent_id', $id)
            ->orderBy('stock_adjustment_details.id', 'asc')
            ->select(
                'stock_adjustment_details.id',
                'stock_adjustment_details.new_quantity',
                'stock_adjustment_details.notes',
                'products.id as product_id',
                'products.name as product_name',
                'products.stock as old_quantity',
                'products.uom',
            )
            ->get();

        return inertia('stock-adjustment/Editor', [
            'item' => $item,
            'details' => $details
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'datetime' => ['required', 'date'], // atau gunakan: 'date_format:Y-m-d H:i:s'
            'type' => [
                'required',
                Rule::in(array_keys(StockAdjustment::Types)),
            ],
            'notes' => 'nullable|string|max:1000',
        ]);

        $item = StockAdjustment::findOrFail($request->id);
        $item->fill($validated);

        $details = $request->post('details', []);

        DB::beginTransaction();

        $total_cost = 0;
        $total_price = 0;

        $stored_details = StockAdjustmentDetail::where('parent_id', $item->id)->get()->keyBy('id');

        foreach ($details as $d) {
            $detail = $stored_details[$d['id']];
            $detail->new_quantity = floatval($d['new_quantity']);
            $detail->balance = $detail->new_quantity - $detail->old_quantity;
            $detail->subtotal_cost = $detail->balance * $detail->cost;
            $detail->subtotal_price = $detail->balance * $detail->price;
            $detail->save();

            if ($request->post('action') === 'close') {
                // update stok
                DB::update('UPDATE products SET stock=? where id=?', [$detail->new_quantity, $detail->product_id]);

                // simpan riwayat perubahan stok
                $stockMovement = new StockMovement([
                    'product_id' => $detail->product_id,
                    'ref_id' => $detail->id,
                    'ref_type' => StockMovement::RefType_StockAdjustment,
                    'quantity' => $detail->balance,
                ]);
                $stockMovement->save();
            }

            $total_cost += $detail->subtotal_cost;
            $total_price += $detail->subtotal_price;
        }

        $next_url = 'admin.stock-adjustment.editor';

        if ($request->post('action') === 'cancel') {
            $item->status = StockAdjustment::Status_Cancelled;
            $next_url = 'admin.stock-adjustment.index';
        } else if ($request->post('action') === 'close') {
            $item->status = StockAdjustment::Status_Closed;
            $next_url = 'admin.stock-adjustment.index';
        }

        $item->total_cost = $total_cost;
        $item->total_price = $total_price;
        $item->save();
        DB::commit();

        return redirect(route($next_url, [
            'id' => $item->id
        ]),)->with([
            'message' => __('messages.stock-adjustment-saved', ['id' => $item->id])
        ]);
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = StockAdjustment::findOrFail($id);

        DB::beginTransaction();

        if ($item->status == StockAdjustment::Status_Closed) {
            $details = StockAdjustmentDetail::where('parent_id', $item->id)->get()->keyBy('product_id');

            // Ambil produk terkait
            $products = Product::whereIn('id', array_keys($details->all()))->get();

            foreach ($products as $product) {
                $detail = $details[$product->id];
                $product->stock += (-$detail->balance); // refund stok
                $product->save();

                // Hapus stock movement terkait detail ini
                DB::delete(
                    'DELETE FROM stock_movements WHERE ref_type = ? AND ref_id = ?',
                    [StockMovement::RefType_StockAdjustment, $detail->id]
                );
            }
        }

        DB::delete('delete from stock_adjustment_details where parent_id=?', [$item->id]);

        $item->delete();

        DB::commit();

        return response()->json([
            'message' => __('messages.stock-adjustment-deleted', ['id' => $item->id])
        ]);
    }
}
