<?php

namespace Modules\Customer\Http\Controllers;

use App\Helpers\ImageUploaderHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTopUpConfirmation;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Controllers\CustomerWalletTransactionController;

class WalletTopUpConfirmationController extends Controller
{

    public function index()
    {
        return inertia('wallet-topup-confirmation/Index', []);
    }

    public function add()
    {
        return inertia('wallet-topup-confirmation/Editor', [
            'accounts' => FinanceAccount::where('has_wallet_access', true)->orderBy('name')->get()
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $currentUserId = Auth::guard('customer')->user()->id;

        $q = CustomerWalletTransactionConfirmation::with(['financeAccount:id,name,bank,number,holder'])
            ->where('customer_id', $currentUserId);

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

        if (
            !empty($filter['status']) && $filter['status'] !== 'all'
            && in_array($filter['status'], array_keys(CustomerWalletTransactionConfirmation::Statuses))
        ) {
            $q->where('status', '=', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'finance_account_id' => 'required|exists:finance_accounts,id',
            'datetime' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:15120',
            'image_path' => 'nullable|string',
        ], [
            'finance_account_id.required' => 'Akun tujuan harus diisi.',
            'finance_account_id.exists' => 'Akun tujuan tidak ditemukan.',
            'datetime.required' => 'Waktu harus diisi.',
            'datetime.date' => 'Format waktu tidak valid.',
            'amount.required' => 'Jumlah topup harus diisi.',
            'amount.numeric' => 'Jumlah topup tidak valid.',
            'amount.min' => 'Jumlah topup harus diisi.',
            'notes.string' => 'Keterangan tidak valid.',
            'notes.max' => 'Keterangan maksimal 50 karakter.'
        ]);

        $validated['customer_id'] = Auth::guard('customer')->user()->id;
        $validated['status'] = CustomerWalletTransactionConfirmation::Status_Pending;

        DB::beginTransaction();
        $newlyUploadedImagePath = null;

        try {
            $topUpConfirmation = new CustomerWalletTransactionConfirmation();

            unset($validated['image']);
            $validated['image_path'] = '';

            $topUpConfirmation->fill($validated);
            $topUpConfirmation->save();

            if ($request->hasFile('image')) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $request->file('image'),
                    'wallet-topup-confirmations', // FIXME: kalo bisa jangan hardcode karena dibutuhkan di modul lain!
                );
            }

            // Perbarui path gambar di model transaksi jika ada perubahan
            if ($topUpConfirmation->image_path !== $newlyUploadedImagePath) {
                $topUpConfirmation->image_path = $newlyUploadedImagePath;
                $topUpConfirmation->save(); // Simpan kembali transaksi untuk memperbarui image_path di DB
            }

            DB::commit();

            return redirect(route('customer.wallet-topup-confirmation.index'))
                ->with('success', 'Konfirmasi topup telah disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newlyUploadedImagePath && file_exists(public_path($newlyUploadedImagePath))) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $e;
        }
    }
}
