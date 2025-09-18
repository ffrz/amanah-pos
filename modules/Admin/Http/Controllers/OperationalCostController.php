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

use App\Helpers\ImageUploaderHelper;
use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalCostController extends Controller
{
    protected function _categories()
    {
        return OperationalCostCategory::all();
    }

    protected function _financeAccounts()
    {
        // FIXME: Disini mungkin user hanya boleh catat pengeluaran dari akun yang diperbolehkan saja
        return FinanceAccount::where('active', '=', true)
            ->orderBy('name')->get();
    }

    public function index()
    {
        return inertia('operational-cost/Index', [
            'categories' => $this->_categories(),
        ]);
    }

    public function detail($id)
    {
        $item = OperationalCost::with(['financeAccount', 'category', 'creator', 'updater'])
            ->findOrFail($id);

        return inertia('operational-cost/Detail', [
            'data' => $item
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = OperationalCost::with('category');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id'])) {
            if ($filter['category_id'] === 'null') {
                $q->whereNull('category_id');
            } else if ($filter['category_id'] !== 'all') {
                $q->where('category_id', '=', $filter['category_id']);
            }
        }

        // Tambahan filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'null') {
            $q->whereYear('date', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'null') {
                $q->whereMonth('date', $filter['month']);
            }
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = OperationalCost::findOrFail($id);
        $item->id = null;
        return inertia('operational-cost/Editor', [
            'data' => $item,
            'categories' => $this->_categories(),
            '_financeAccounts' => $this->_financeAccounts(),
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? OperationalCost::findOrFail($id) : new OperationalCost(['date' => date('Y-m-d')]);
        return inertia('operational-cost/Editor', [
            'data' => $item,
            'categories' => $this->_categories(),
            '_financeAccounts' => $this->_financeAccounts(),
        ]);
    }

    public function save(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'finance_account_id' => 'nullable|exists:_financeAccounts,id',
            'date'               => 'required|date',
            'category_id'        => 'nullable',
            'description'        => 'required|max:255',
            'amount'             => 'required|numeric|gt:0',
            'notes'              => 'nullable|max:1000',
            'image_path'         => 'nullable|string',
            'image'              => 'nullable|image|max:15120',
        ]);

        $validated['notes'] = $validated['notes'] ?? '';

        DB::beginTransaction();

        // Inisialisasi variabel untuk rollback
        $oldItem = null;
        $newlyUploadedImagePath = null;

        try {
            // 2. Tentukan item dan ambil data lama jika mode edit
            if ($request->id) {
                $item = OperationalCost::findOrFail($request->post('id'));
                $oldItem = clone $item; // Simpan data lama untuk perbandingan
            } else {
                $item = new OperationalCost();
            }

            // 3. PENANGANAN GAMBAR (IMAGE)
            $oldImagePath = $oldItem ? $oldItem->image_path : null;

            if ($request->hasFile('image')) {
                // Upload file baru dan hapus yang lama (helper yang tangani)
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $request->file('image'),
                    'operational-costs',
                    $oldImagePath
                );
                $validated['image_path'] = $newlyUploadedImagePath;
            } elseif ($request->input('image_path') === null) {
                // User hapus gambar lama, hapus dari storage
                if ($oldImagePath) {
                    ImageUploaderHelper::deleteImage($oldImagePath);
                }
                $validated['image_path'] = null;
            } else {
                // Pertahankan gambar lama, tidak perlu ubah validated
            }

            unset($validated['image']);

            // 4. SIMPAN DATA OPERASIONAL
            $item->fill($validated);
            $item->save();

            // 5. PENANGANAN TRANSAKSI KEUANGAN
            // Logika pengembalian saldo hanya perlu dilakukan jika ada perubahan akun
            if ($oldItem && $oldItem->finance_account_id !== $item->finance_account_id) {
                if ($oldItem->finance_account_id) {
                    $oldAccount = FinanceAccount::findOrFail($oldItem->finance_account_id);
                    $oldAccount->balance += abs($oldItem->amount);
                    $oldAccount->save();
                }

                // tambahan, jika hapus akun id, maka transaksinya juga harus dihapus
                if ($oldItem->finance_account_id && !$item->finance_account_id) {
                    FinanceTransaction::where('ref_id', $item->id)
                        ->where('ref_type', FinanceTransaction::RefType_OperationalCost)
                        ->delete();
                }
            }

            $this->updateOrCreateFinanceTransaction($item);

            DB::commit();

            return redirect(route('admin.operational-cost.index'))
                ->with('success', "Biaya $item->description telah disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            // Hapus gambar baru jika ada error
            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $e;
        }
    }


    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = OperationalCost::findOrFail($id);

        DB::beginTransaction();
        $item->delete();

        // Cari transaksi yang sudah ada
        $transaction = FinanceTransaction::where('ref_id', $item->id)
            ->where('ref_type', FinanceTransaction::RefType_OperationalCost)
            ->first();

        if ($transaction) {
            $account = $transaction->account;
            $account->balance += abs($transaction->amount);

            $account->save();
            $transaction->delete();
        }

        if ($item->image_path) {
            ImageUploaderHelper::deleteImage($item->image_path);
        }

        DB::commit();

        return response()->json([
            'message' => "Biaya operasional $item->description telah dihapus."
        ]);
    }

    private function updateOrCreateFinanceTransaction(OperationalCost $item)
    {
        // Jika tidak ada akun keuangan yang dipilih, kembalikan null
        if (!$item->finance_account_id) {
            return;
        }

        // Kurangi saldo akun keuangan
        $account = FinanceAccount::findOrFail($item->finance_account_id);
        $account->balance -= abs($item->amount);
        $account->save();

        // Cari transaksi yang sudah ada
        $transaction = FinanceTransaction::where('ref_id', $item->id)
            ->where('ref_type', FinanceTransaction::RefType_OperationalCost)
            ->first();

        // Jika transaksi sudah ada, perbarui
        if ($transaction) {
            $transaction->account_id = $item->finance_account_id;
            $transaction->amount = -abs($item->amount);
            $transaction->save();
            return;
        }

        // Jika belum ada, buat yang baru
        $transaction = FinanceTransaction::create([
            'account_id' => $account->id,
            'datetime' => new Carbon($item->date),
            'amount' => -abs($item->amount),
            'type' => FinanceTransaction::Type_Expense,
            'ref_type' => FinanceTransaction::RefType_OperationalCost,
            'ref_id' => $item->id,
            'notes' => "Biaya operasional #$item->id",
        ]);
    }
}
