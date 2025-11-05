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

use App\Helpers\AutoResponseHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Http\Requests\Customer\GetDataRequest;
use Modules\Admin\Http\Requests\Customer\SaveRequest;
use Modules\Admin\Services\CustomerService;
use Modules\Admin\Services\CustomerWalletTransactionService;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Customer::class);

        return inertia('customer/Index');
    }

    public function detail($id = 0)
    {
        $item = $this->customerService->find($id);

        $this->authorize('view', $item);

        return inertia('customer/Detail', [
            'data' => $item,
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', Customer::class);

        $items = $this->customerService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $this->authorize('create', Customer::class);

        $item = $this->customerService->duplicate($id);

        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        $item = $this->customerService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        $data = $item->toArray();
        $data['password'] = '12345';

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($data);
        }

        return inertia('customer/Editor', [
            'data' => $data
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->customerService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $this->customerService->save($item, $request->validated());

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return redirect(route('admin.customer.detail', $item->id))
            ->with('success', "Pelanggan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->customerService->find($id);

        $this->authorize('delete', $item);

        $this->customerService->delete($item);

        return JsonResponseHelper::success($item, "Pelanggan $item->name telah dihapus.");
    }

    public function getBalance(Request $request)
    {
        $customer = Customer::findOrFail($request->id);
        return response()->json(['wallet_balance' => $customer->wallet_balance]);
    }

    public function import(Request $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {

            $default_password = Hash::make('12345');

            // Validasi file yang diunggah
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');

            // Buka file untuk dibaca
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $separator = ';';

                // Dapatkan header dari baris pertama untuk pemetaan kolom
                $header = fgetcsv($handle, 1000, ';');
                $first_line = fgets($handle, 5000);
                if (!$first_line) {
                    fclose($handle);
                    throw new Exception('Header tidak terdeteksi');
                }

                $comma_count = substr_count($first_line, ',');
                $semicolon_count = substr_count($first_line, ';');
                if ($semicolon_count > $comma_count) {
                    $separator = ';';
                }

                rewind($handle);
                $header = fgetcsv($handle, 5000, $separator);

                // Mulai transaksi database
                DB::beginTransaction();

                try {
                    while (($data = fgetcsv($handle, 1000, $separator)) !== false) {

                        // Pastikan baris data tidak kosong
                        if (empty(array_filter($data, 'strlen'))) {
                            continue;
                        }

                        // Gabungkan header dan data untuk membuat array yang mudah diakses

                        $row = array_combine($header, $data);

                        // --- Perubahan di sini: bersihkan Saldo ---
                        $cleanedBalance = str_replace('.', '', $row['wallet_balance'] ?? 0);
                        $cleanedBalance = str_replace([',00', ','], '', $cleanedBalance);
                        $initialBalance = (float) $cleanedBalance;

                        $code = $row['code'] ?? $this->customerService->generateCustomerCode();

                        $customer = Customer::updateOrCreate(
                            [
                                'id'   => $row['id'] ?? null,
                                'code' => $row['code'] ?? $this->customerService->generateCustomerCode(),
                            ],
                            [
                                'type'     => Customer::Type_General,
                                'name'     => trim($row['name'] ?? ''),
                                'address'  => trim($row['address'] ?? ''),
                                'phone'    => trim($row['phone'] ?? ''),
                                'password' => $default_password,
                                'balance'  => $row['balance'] ?? 0,
                                // wallet balance sudah otomatis dihandle CustomerWalletTransactionService::handleTransaction
                            ]
                        );

                        if ($customer->wasRecentlyCreated && $initialBalance > 0) {
                            app(CustomerWalletTransactionService::class)
                                ->handleTransaction([
                                    'customer_id' => $customer->id,
                                    'finance_account_id' => $newData['finance_account_id'] ?? null,
                                    'datetime' => now(),
                                    'amount' => $initialBalance,
                                    'type' => CustomerWalletTransaction::Type_Adjustment,
                                    'notes' => 'Saldo awal dari import',
                                ]);
                        }
                    }

                    DB::commit();

                    fclose($handle);
                    return redirect()->back()->with('success', 'Data pelanggan berhasil diimpor!');
                } catch (\Exception $e) {
                    DB::rollBack();
                    fclose($handle);

                    return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('error', 'Gagal membuka file.');
        }

        return inertia('customer/Import');
    }
}
