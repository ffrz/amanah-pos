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
use App\Models\FinanceAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        allowed_roles(User::Role_Admin);
    }

    public function index()
    {
        return inertia('user/Index');
    }

    public function detail($id = 0)
    {
        return inertia('user/Detail', [
            'data' => User::with(['cashierAccount'])->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = User::with(['cashierAccount']);

        $q->orderBy($orderBy, $orderType);

        if (!empty($filter['role'] && $filter['role'] != 'all')) {
            $q->where('role', '=', $filter['role']);
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        $users = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($users);
    }

    public function duplicate($id)
    {
        $user = User::findOrFail($id);
        $user->id = null;
        $user->created_at = null;
        return inertia('user/Editor', [
            'data' => $user,
        ]);
    }

    public function editor($id = 0)
    {
        $user = $id ? User::findOrFail($id) : new User();

        if (!$id) {
            $user->active = true;
            $user->admin = true;
        } else if ($user == Auth::user()) {
            // return redirect(route('admin.user.index'))->with('warning', 'TIdak dapat mengubah akun anda sendiri.');
        }

        return inertia('user/Editor', [
            'data' => $user,
            'finance_accounts' => FinanceAccount::where('type', '=', FinanceAccount::Type_CashierCash)
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    public function save(Request $request)
    {
        $isNew = empty($request->id);
        $password = $request->get('password');

        $rules = [
            'name'     => 'required|max:255',
            'role'     => 'required',
            'username' => [
                'required',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($request->id),
            ],
        ];

        $create_cash_account = $request->post('cashier_account_id') === 'new';

        if (!$create_cash_account) {
            $rules['cashier_account_id'] = ['nullable', Rule::exists('finance_accounts', 'id')];
        }

        if ($isNew || !empty($password)) {
            $rules['password'] = 'required|min:5|max:40';
        }

        $validated = $request->validate($rules);

        $validated['cashier_account_id'] = $validated['cashier_account_id'] ?? null;

        $user = $isNew ? new User() : User::findOrFail($request->id);

        $fields = ['name', 'username', 'role', 'active', 'cashier_account_id'];
        $user->fill($request->only($fields));

        if (!empty($password)) {
            $user->password = Hash::make($password);
        }

        try {
            DB::beginTransaction();

            if ($create_cash_account) {
                $baseName = 'Kas ' . $user->username;
                $accountName = $baseName;
                $suffix = 2;

                // Loop untuk memastikan nama akun unik
                while (FinanceAccount::where('name', $accountName)->exists()) {
                    $accountName = $baseName . ' ' . $suffix++;
                }

                $cashAccount = FinanceAccount::create([
                    'name'    => $accountName,
                    'balance' => 0,
                    'type'    => FinanceAccount::Type_CashierCash,
                    'active'  => true,
                    'notes'   => 'Kas kasir dibuat otomatis saat pengguna dibuat.',
                ]);

                $user->cashier_account_id = $cashAccount->id;
            }

            $user->save();

            DB::commit();


            $action = $isNew ? 'ditambahkan' : 'diperbarui';
            $message = "Pengguna {$user->username} telah {$action}.";

            return redirect(route('admin.user.index'))->with('success', $message);
        } catch (\Throwable $ex) {
            DB::rollBack();
            // Log error secara lengkap untuk debugging
            // \Log::error("Gagal menyimpan pengguna. Detail: " . $ex->getMessage());

            // Tampilkan pesan yang lebih umum ke pengguna
            return redirect(route('admin.user.index'))->with('error', 'Terjadi kesalahan. Gagal menyimpan pengguna. Silakan coba lagi.');
        }
    }


    public function delete($id)
    {
        $user = User::findOrFail($id);

        if ($user->id == Auth::user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri!'
            ], 409);
        }

        $user->delete();

        return response()->json([
            'message' => "Pengguna {$user->username} telah dihapus!"
        ]);
    }
}
