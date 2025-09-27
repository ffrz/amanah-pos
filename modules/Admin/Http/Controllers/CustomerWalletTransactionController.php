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
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceTransaction;
use App\Services\CommonDataService;
use App\Services\CustomerWalletTransactionService;
use App\Services\FinanceTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CustomerWalletTransactionController extends Controller
{

    protected $commonDataService;
    protected $financeTransactionService;
    protected $customerWalletTransactionService;

    public function __construct(
        CommonDataService $commonDataService,
        FinanceTransactionService $financeTransactionService,
        CustomerWalletTransactionService $customerWalletTransactionService
    ) {
        $this->commonDataService = $commonDataService;
        $this->financeTransactionService = $financeTransactionService;
        $this->customerWalletTransactionService = $customerWalletTransactionService;
    }

    public function index()
    {
        return inertia('customer-wallet-transaction/Index', []);
    }

    public function detail($id)
    {
        $trx = CustomerWalletTransaction::with(['customer', 'creator', 'updater'])->findOrFail($id);

        return inertia('customer-wallet-transaction/Detail', [
            'data' => $trx
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with(['customer', 'financeAccount']);

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

        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', '=', $filter['customer_id']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        $item = $id ? CustomerWalletTransaction::findOrFail($id) : new CustomerWalletTransaction(['datetime' => date('Y-m-d H:i:s')]);
        return inertia('customer-wallet-transaction/Editor', [
            'data' => $item,
            'customers' => $this->commonDataService->getCustomers(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'finance_account_id' => 'sometimes|nullable|exists:finance_accounts,id',
            'datetime' => 'required|date',
            'type' => 'required|in:' . implode(',', array_keys(CustomerWalletTransaction::Types)),
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
            'image_path' => 'nullable|string',
            'image' => 'nullable|image|max:15120',
        ]);

        $newlyUploadedImagePath = null;

        try {
            DB::beginTransaction();

            if (
                in_array($validated['type'], [
                    CustomerWalletTransaction::Type_SalesOrderPayment,
                    CustomerWalletTransaction::Type_Withdrawal,
                ])
            ) {
                $validated['amount'] *= -1;
            }

            $amount = $validated['amount'];

            $validated['notes'] ?? '';

            if ($request->hasFile('image')) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $request->file('image'),
                    'customer-wallet-transactions'
                );
                $validated['image_path'] = $newlyUploadedImagePath;
                unset($validated['image']);
            }

            // TODO: pindahkan ke service

            $item = $this->customerWalletTransactionService->handleTransaction($validated);

            // hanya digunakan ketika mempengaruhi akun kas
            if (
                in_array($validated['type'], [
                    CustomerWalletTransaction::Type_Deposit,
                    CustomerWalletTransaction::Type_Withdrawal,
                ]) && $validated['finance_account_id']
            ) {
                $this->financeTransactionService->handleTransaction([
                    'datetime' => $validated['datetime'],
                    'account_id' => $validated['finance_account_id'],
                    'amount' => $amount,
                    'type' => $amount >= 0 ? FinanceTransaction::Type_Income : FinanceTransaction::Type_Expense,
                    'notes' => 'Transaksi wallet customer ' . $item->customer->code . ' Ref: ' . $item->formatted_id,
                    'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
                    'ref_id' => $item->id,
                ]);
            }

            DB::commit();

            return redirect(route('admin.customer-wallet-transaction.index'))
                ->with('success', "Transaksi $item->formatted_id telah disimpan.");
        } catch (\Throwable $ex) {
            DB::rollBack();
            // Hapus gambar baru jika ada error
            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }
            throw $ex;
        }
    }

    public function adjustment(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('customer-wallet-transaction/Adjustment', [
                'data' => [],
                'customers' => $this->commonDataService->getCustomers()
            ]);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'new_wallet_balance' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
        ]);

        try {


            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);

            $this->customerWalletTransactionService->handleTransaction([
                'customer_id' => $customer->id,
                'datetime' => Carbon::now(),
                'type' => CustomerWalletTransaction::Type_Adjustment,
                'amount' => $validated['new_wallet_balance'] - $customer->wallet_balance,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect(route('admin.customer-wallet-transaction.index'))
                ->with('success', "Penyesuaian saldo $customer->name telah disimpan.");
        } catch (Throwable $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function delete($id)
    {
        $item = CustomerWalletTransaction::findOrFail($id);

        // Jangan perbolehkan penghapusan transaksi yang dibuat dari modul lain
        if ($item->ref_type) {
            return JsonResponseHelper::error('Rekaman tidak dapat dihapus karena berrelasi dengan transaksi lain.', 403);
        }

        try {
            DB::beginTransaction();

            // restore customer wallet_balance
            $this->customerWalletTransactionService->addToBalance($item->customer, -$item->amount);

            // Jika transaksi menyentuh kas, kembalikan saldo kas
            if (in_array($item->type, [
                CustomerWalletTransaction::Type_Deposit,
                CustomerWalletTransaction::Type_Withdrawal
            ]) && $item->finance_account_id) {
                $this->financeTransactionService->reverseTransaction($item->id, FinanceTransaction::RefType_CustomerWalletTransaction);
            }

            ImageUploaderHelper::deleteImage($item->image_path);

            $item->delete();
            DB::commit();

            return JsonResponseHelper::success(
                $item,
                "Transaksi #$item->formatted_id telah dihapus."
            );
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error('Terdapat kesalahan saat menghapus rekaman.', 500, $ex);
        }
    }
}
