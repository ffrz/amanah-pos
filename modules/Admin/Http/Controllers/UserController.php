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
            'data' => User::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = User::query();

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
            'data' => $user
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
            'active'   => 'required|boolean'
        ];

        if ($isNew || !empty($password)) {
            $rules['password'] = 'required|min:5|max:40';
        }

        $validated = $request->validate($rules);
        $user = $isNew ? new User() : User::findOrFail($request->id);
        $user->fill($validated);

        if (!empty($password)) {
            $user->password = Hash::make($password);
        }

        try {
            DB::beginTransaction();
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
            return JsonResponseHelper::error('Tidak dapat menghapus akun sendiri!', 409);
        }

        $user->delete();

        return JsonResponseHelper::success($user, "Pengguna {$user->username} telah dihapus!");
    }
}
