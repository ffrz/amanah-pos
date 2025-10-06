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
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    public function editor($id = 0)
    {
        $item = $this->customerService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->customerService->findOrCreate($request->id);

        $this->customerService->save($item, $request->validated());

        return redirect(route('admin.customer.index'))->with('success', "Pelanggan $item->name telah disimpan.");
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

                // Dapatkan header dari baris pertama untuk pemetaan kolom
                $header = fgetcsv($handle, 1000, ';');

                // Mulai transaksi database
                DB::beginTransaction();

                try {
                    // Loop melalui setiap baris data
                    // FIXME: kita butuh konsistensi separator
                    // bisa autodetect atau diset saat import
                    while (($data = fgetcsv($handle, 1000, ';')) !== false) {

                        // Pastikan baris data tidak kosong
                        if (empty(array_filter($data, 'strlen'))) {
                            continue;
                        }

                        // Gabungkan header dan data untuk membuat array yang mudah diakses

                        $row = array_combine($header, $data);
                        // Ambil data yang dibutuhkan dan bersihkan (trim)
                        $accountNumber  = trim($row['No. Rekening']);
                        $name           = trim($row['Nama']);
                        $address        = trim($row['Alamat']);
                        $phone          = trim($row['No. HP']);

                        // --- Perubahan di sini: bersihkan Saldo ---
                        $cleanedBalance = str_replace('.', '', $row['Saldo']);
                        $cleanedBalance = str_replace([',00', ','], '', $cleanedBalance);
                        $initialBalance = (float) $cleanedBalance;

                        // Buat code otomatis: 5 karakter awal nama + 5 karakter akhir no. rekening
                        $name_sanitized = str_replace([' ', '.'], '', $name);
                        $first_five_name = substr($name_sanitized, 0, 5);
                        $last_five_account = substr($accountNumber, -5);

                        $code = strtolower($first_five_name . $last_five_account);

                        // Cari atau buat customer berdasarkan nomor rekening
                        $customer = Customer::updateOrCreate(
                            ['code' => $code,],
                            [
                                'type'     => Customer::Type_General,
                                'name'     => $name,
                                'address'  => $address,
                                'phone'    => $phone,
                                'password' => $default_password,
                            ]
                        );

                        if ($customer->wasRecentlyCreated) {
                            if ($initialBalance > 0) {
                                CustomerWalletTransactionService::handleTransaction([
                                    'customer_id' => $customer->id,
                                    'finance_account_id' => $newData['finance_account_id'] ?? null,
                                    'datetime' => now(),
                                    'amount' => $initialBalance,
                                    'type' => CustomerWalletTransaction::Type_Adjustment,
                                    'notes' => 'Saldo awal dari import',
                                ]);
                            }
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
