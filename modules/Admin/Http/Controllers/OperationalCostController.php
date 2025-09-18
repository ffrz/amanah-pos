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
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\User;
use App\Services\CommonDataService;
use App\Services\FinanceTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalCostController extends Controller
{
    protected $commonDataService;
    protected $financeTransactionService;

    public function __construct(
        CommonDataService $commonDataService,
        FinanceTransactionService $financeTransactionService
    ) {
        $this->commonDataService = $commonDataService;
        $this->financeTransactionService = $financeTransactionService;
    }

    public function index()
    {
        return inertia('operational-cost/Index', [
            'categories' => $this->commonDataService->getOperationalCategories(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
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

        $q = OperationalCost::with(['category', 'financeAccount']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id']) && $filter['category_id'] !== 'all') {
            if ($filter['category_id'] === null) {
                $q->whereNull('category_id');
            } else if ($filter['category_id'] !== 'all') {
                $q->where('category_id', '=', $filter['category_id']);
            }
        }

        if (!empty($filter['finance_account_id']) && $filter['finance_account_id'] !== 'all') {
            $q->where('finance_account_id', '=', $filter['finance_account_id']);
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
        return $this->renderEditor($item);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? OperationalCost::findOrFail($id) : new OperationalCost(['date' => date('Y-m-d')]);
        return $this->renderEditor($item);
    }

    private function renderEditor($item)
    {
        return inertia('operational-cost/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getOperationalCategories(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function save(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'finance_account_id' => 'nullable|exists:finance_accounts,id',
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
            $this->financeTransactionService->handleTransaction(
                [
                    'ref_id'     => $item->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'datetime'   => new Carbon($item->date),
                    'account_id' => $item->finance_account_id,
                    'amount'     => -abs($item->amount),
                    'type'       => FinanceTransaction::Type_Expense,
                    'notes'      => "Biaya operasional #$item->id",
                ],
                $oldItem ? [
                    'ref_id'     => $oldItem->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'account_id' => $oldItem->finance_account_id,
                    'amount'     => -abs($oldItem->amount)
                ] : []
            );

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

        try {
            DB::beginTransaction();

            $item->delete();

            $this->financeTransactionService->reverseTransaction($item->id, FinanceTransaction::RefType_OperationalCost);

            ImageUploaderHelper::deleteImage($item->image_path);

            DB::commit();

            return JsonResponseHelper::success(
                $item,
                "Biaya operasional $item->description telah dihapus."
            );
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error("Gagal menghapus rekaman.", 500, $ex);
        }
    }
}
