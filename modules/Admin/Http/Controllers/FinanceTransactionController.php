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
use App\Models\FinanceTransaction;
use App\Services\CommonDataService;
use App\Services\FinanceTransactionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceTransactionController extends Controller
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
        return inertia('finance-transaction/Index', [
            'accounts' => $this->commonDataService->getFinanceAccounts()
        ]);
    }

    public function detail($id)
    {
        $item = FinanceTransaction::with(['account', 'creator', 'updater'])
            ->findOrFail($id);

        return inertia('finance-transaction/Detail', [
            'data' => $item
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = FinanceTransaction::with('account');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', $filter['type']);
        }

        if (!empty($filter['account_id']) && $filter['account_id'] !== 'all') {
            $q->where('account_id', $filter['account_id']);
        }

        if (!empty($filter['user_id']) && $filter['user_id'] !== 'all') {
            $q->where('created_by', $filter['user_id']);
        }

        if (!empty($filter['from_datetime'])) {
            $start = Carbon::parse($filter['from_datetime']);
            $end = empty($filter['to_datetime']) ? Carbon::now() : Carbon::parse($filter['to_datetime']);
            $q->whereBetween('created_at', [$start, $end]);
        }


        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        $item = $id ? FinanceTransaction::findOrFail($id) : new FinanceTransaction(['datetime' => Carbon::now()]);
        return inertia('finance-transaction/Editor', [
            'data' => $item,
            'accounts' => $this->commonDataService->getFinanceAccounts()
        ]);
    }

    public function save(Request $request)
    {
        $route = 'admin.finance-transaction.index';
        $validated = $request->validate([
            'account_id'    => 'required|exists:finance_accounts,id',
            'to_account_id' => 'nullable|exists:finance_accounts,id|different:account_id',
            'datetime'      => 'required|date',
            'type'          => 'required|in:' . implode(',', array_keys(FinanceTransaction::Types)),
            'amount'        => 'required|numeric|min:0.01',
            'notes'         => 'nullable|string|max:255',
            'image_path'    => 'nullable|string',
            'image'         => 'nullable|image|max:15120',
        ]);

        $validated['notes'] ?? '';
        $newlyUploadedImagePath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $request->file('image'),
                    'finance-transactions'
                );
                $validated['image_path'] = $newlyUploadedImagePath;
                unset($validated['image']);
            }

            if ($validated['type'] === FinanceTransaction::Type_Transfer && !empty($validated['to_account_id'])) {
                // Transaksi keluar dari akun asal
                $fromTransaction = new FinanceTransaction();
                $fromTransaction->fill([
                    'account_id' => $validated['account_id'],
                    'datetime'   => $validated['datetime'],
                    'type'       => FinanceTransaction::Type_Transfer,
                    'amount'     => -$validated['amount'], // keluar
                    'notes'      => 'Transfer ke akun #' . $validated['to_account_id'] . '. ' . $validated['notes'],
                    'image_path' => $validated['image_path'],
                ]);
                $fromTransaction->save();

                // Update saldo akun asal
                $this->financeTransactionService->addToBalance($fromTransaction->account, $fromTransaction->amount);

                // Transaksi masuk ke akun tujuan
                $toTransaction = new FinanceTransaction();
                $toTransaction->fill([
                    'account_id' => $validated['to_account_id'],
                    'datetime'   => $validated['datetime'],
                    'type'       => FinanceTransaction::Type_Transfer,
                    'amount'     => $validated['amount'], // masuk
                    'notes'      => 'Transfer dari akun #' . $validated['account_id'] . '. ' . $validated['notes'],
                    'image_path' => $validated['image_path'],
                ]);
                $toTransaction->save();

                // Update saldo akun tujuan
                $this->financeTransactionService->addToBalance($toTransaction->account, $toTransaction->amount);

                // Link untuk keperluan delete
                $fromTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
                $fromTransaction->ref_id = $toTransaction->id;
                $fromTransaction->save();

                $toTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
                $toTransaction->ref_id = $fromTransaction->id;
                $toTransaction->save();

                DB::commit();

                return redirect(route($route))
                    ->with('success', "Transfer antar akun berhasil disimpan.");
            } else {
                // Handle transaksi biasa (income / expense)
                $amount = $validated['amount'];
                if ($validated['type'] === FinanceTransaction::Type_Expense) {
                    $amount = -$amount;
                }

                $transaction = new FinanceTransaction();
                $transaction->fill([
                    'account_id'  => $validated['account_id'],
                    'datetime'    => $validated['datetime'],
                    'type'        => $validated['type'],
                    'amount'      => $amount,
                    'notes'       => $validated['notes'],
                    'image_path'  => $validated['image_path'],
                ]);
                $transaction->save();

                // update saldo
                $this->financeTransactionService->addToBalance($transaction->account, $transaction->amount);

                DB::commit();

                return redirect(route($route))
                    ->with('success', "Transaksi $transaction->formatted_id telah disimpan.");
            }
        } catch (Exception $ex) {
            DB::rollBack();

            // Hapus gambar baru jika ada error
            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $ex;
        }
    }

    public function delete($id)
    {

        try {
            $item = FinanceTransaction::findOrFail($id);
            if ($item->ref_type && $item->ref_type != FinanceTransaction::RefType_FinanceTransaction) {
                return JsonResponseHelper::error("
                Transaksi #$item->id tidak dapat dihapus karena berkaitan dengan transaksi lainnya.
                Ref type: $item->ref_type. Ref ID: $item->ref_id", 403);
            }

            DB::beginTransaction();

            $this->financeTransactionService->addToBalance($item->account, -$item->amount);

            if ($item->type === FinanceTransaction::Type_Transfer && $item->ref_type === FinanceTransaction::RefType_FinanceTransaction && $item->ref_id) {
                $pair = FinanceTransaction::find($item->ref_id);
                if ($pair) {
                    // Kembalikan saldo akun tujuan
                    $this->financeTransactionService->addToBalance($pair->account, -$pair->amount);

                    // Hapus pasangan
                    $pair->delete();
                    ImageUploaderHelper::deleteImage($pair->image_path);
                }
            }

            $item->delete();
            ImageUploaderHelper::deleteImage($item->image_path);

            DB::commit();
        } catch (Exception $ex) {
            return JsonResponseHelper::error("Terdapat kesalahan saat menghapus transaksi $item->formatted_id", 500, $ex);
        }

        return JsonResponseHelper::success($item, "Transaksi #$item->formatted_id telah dihapus.");
    }
}
