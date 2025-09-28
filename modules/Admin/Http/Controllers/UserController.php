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
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('user/Index');
    }

    /**
     * Tampilkan halaman detail pengguna.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function detail($id = 0)
    {
        return inertia('user/Detail', [
            'data' => User::with('roles')->findOrFail($id),
        ]);
    }

    /**
     * Dapatkan data pengguna dalam format paginasi, dengan filter peran dari Spatie.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = User::with(['roles']);

        // Filter berdasarkan peran dari Spatie
        if (!empty($filter['role']) && $filter['role'] != 'all') {
            $q->whereHas('roles', function ($query) use ($filter) {
                $query->where('id', $filter['role']);
            });
        }

        // Filter berdasarkan status
        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        // Filter berdasarkan pencarian nama atau username
        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('username', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $users = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($users);
    }

    /**
     * Duplikasi pengguna.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function duplicate($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $user->id = null;
        $user->created_at = null;

        $roles = Role::orderBy('name', 'asc')->get();

        return inertia('user/Editor', [
            'data' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Tampilkan halaman editor untuk pengguna.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function editor($id = 0)
    {
        $user = $id ? User::with('roles')->findOrFail($id) : new User();

        if (!$id) {
            $user->active = true;
            $user->admin = true;
        } else if ($user->id == Auth::user()->id) {
            return abort(403, 'Tidak dapat mengubah akun sendiri!');
        }

        $roles = Role::orderBy('name', 'asc')->get();

        return inertia('user/Editor', [
            'data' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Simpan pengguna baru atau perbarui pengguna yang sudah ada, termasuk peran.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        if ($request->id == Auth::user()->id) {
            return abort(403, 'Tidak dapat mengubah akun sendiri!');
        }

        $isNew = empty($request->id);
        $password = $request->get('password');

        $rules = [
            'name'      => 'required|max:255',
            'username'  => [
                'required',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($request->id),
            ],
            // Peran sekarang adalah array, bukan lagi string
            'type'      => 'required|string',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:acl_roles,id',
            'active'    => 'required|boolean'
        ];

        if ($isNew || !empty($password)) {
            $rules['password'] = 'required|min:5|max:40';
        }

        $validated = $request->validate($rules);
        $user = $isNew ? new User() : User::findOrFail($request->id);

        try {
            DB::beginTransaction();

            // Isi properti model dari data yang divalidasi
            $user->name = $validated['name'];
            $user->username = $validated['username'];
            $user->active = $validated['active'];
            $user->role = $validated['role'];

            // Jika ada password baru, hash dan simpan
            if (!empty($password)) {
                $user->password = Hash::make($password);
            }

            $user->save();

            // Atur peran pengguna menggunakan Spatie
            // Jika role adalah SuperUser, hapus semua peran spatie agar bisa bypass
            if ($validated['role'] === 'SuperUser') {
                $user->syncRoles([]);
                $user->admin = true;
            } else {
                $user->syncRoles($validated['roles'] ?? []);
                $user->admin = false;
            }

            DB::commit();

            $action = $isNew ? 'ditambahkan' : 'diperbarui';
            $message = "Pengguna {$user->username} telah {$action}.";

            return redirect(route('admin.user.index'))->with('success', $message);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return redirect(route('admin.user.index'))->with('error', 'Terjadi kesalahan. Gagal menyimpan pengguna. Silakan coba lagi.');
        }
    }

    /**
     * Hapus pengguna.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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
