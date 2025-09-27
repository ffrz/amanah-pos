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

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Services\CustomerWalletTransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        return inertia('customer/Index');
    }

    public function detail($id = 0)
    {
        return inertia('customer/Detail', [
            'data' => Customer::with(['creator', 'updater'])->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Customer::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Customer::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            return $this->save($request);
        }

        $item = $id ? Customer::findOrFail($id) : new Customer(['active' => true]);
        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:40|unique:customers,code' . ($request->id ? ',' . $request->id : ''),
            'type' => [
                'required',
                'max:40',
                Rule::in(Customer::Types), // Memastikan nilai 'type' ada di dalam array Customer::TYPES
            ],
            'name' => 'required|max:255',
            'phone' => 'nullable|max:100',
            'address' => 'nullable|max:1000',
            'active'   => 'required|boolean',
            'password' => (!$request->id ? 'required|' : '') . 'min:5|max:40',
        ]);

        $item = !$request->id ? new Customer() : Customer::findOrFail($request->id);
        if (!$request->id) {
            $validated['wallet_balance'] = 0;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.customer.index'))->with('success', "Pelanggan $item->name telah disimpan.");
    }

    public function delete($id)
    {
        throw new Exception('Fitur sementara waktu dinonaktifkan, silahkan hubungi developer!');

        $item = Customer::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Pelanggan $item->name telah dihapus."
        ]);
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
