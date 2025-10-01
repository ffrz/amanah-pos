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

namespace Modules\Admin\Http\Controllers\Settings;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Modules\Admin\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('settings/user/Index');
    }

    /**
     * Tampilkan halaman detail pengguna.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function detail($id = 0)
    {
        return inertia('settings/user/Detail', [
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

        return inertia('settings/user/Editor', [
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

        return inertia('settings/user/Editor', [
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
            'username'  => [
                'required',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($request->id),
            ],
            'name'    => 'required|max:255',
            'type'    => ['required', Rule::in(array_keys(User::Types))],
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:acl_roles,id',
            'active'  => 'required|boolean'
        ];

        if ($isNew || !empty($password)) {
            $rules['password'] = 'required|min:5|max:40';
        }

        $validated = $request->validate($rules);

        unset($validated['password']); // kita gak butuh data password dari validated

        $user = $isNew ? new User() : User::findOrFail($request->id);

        try {
            $oldData = $user->toArray();

            $user->fill($validated);

            $dirtyAttributes = $user->getDirty();

            // Jika ada password baru, hash dan simpan
            if (!empty($password)) {
                $user->password = Hash::make($password);
                $dirtyAttributes['password'] = '********';
            }

            if (empty($dirtyAttributes)) {
                return redirect(route('admin.user.index'))->with('success', "Tidak ada perubahan terdeteksi.");
            }

            DB::beginTransaction();

            $user->save();

            // Jika jenis akun adalah SuperUser, hapus semua peran spatie agar bisa bypass
            if ($user->type === User::Type_SuperUser) {
                $user->syncRoles([]);
            } else {
                $user->syncRoles($validated['roles'] ?? []);
            }

            // catat log
            $user->roles;
            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Create,
                    "Pengguna '$user->username' telah ditambahkan.",
                    $user->toArray(),
                );
                $message = "Pengguna {$user->username} telah ditambahkan.";
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Update,
                    "Pengguna '$user->username' telah diperbarui.",
                    [
                        'new_data' => $user->toArray(),
                        'old_data' => $oldData,
                    ]
                );
                $message = "Pengguna {$user->username} telah diperbarui.";
            }

            DB::commit();

            return redirect(route('admin.user.index'))->with('success', $message);
        } catch (\Throwable $e) {
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

        try {
            DB::beginTransaction();
            $user->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_User,
                UserActivityLog::Name_User_Delete,
                "Pengguna '$user->username' telah dihapus.",
                $user->toArray(),
            );

            DB::commit();

            return JsonResponseHelper::success($user, "Pengguna {$user->username} telah dihapus!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return JsonResponseHelper::error("Gagal menghapus pengguna $user->username", 500, $e);
        }
    }
}
