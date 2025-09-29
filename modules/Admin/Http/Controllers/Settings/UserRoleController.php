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
use App\Models\UserActivityLog;
use App\Services\UserActivityLogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
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
     * Tampilkan halaman indeks peran.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('settings/user-role/Index');
    }

    /**
     * Dapatkan data peran dalam format paginasi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Role::query();

        if (!empty($filter['search'])) {
            $q->where('name', 'like', '%' . $filter['search'] . '%');
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }


    public function detail($id = 0)
    {
        return inertia('settings/user-role/Detail', [
            'data' => Role::with(['permissions', 'users'])->findOrFail($id),
        ]);
    }

    public function duplicate($id)
    {
        $item = Role::with('permissions')->findOrFail($id);
        $item->id = null;
        $permissions = Permission::all()->toArray();

        return inertia('settings/user-role/Editor', [
            'data' => $item,
            'permissions' => $permissions
        ]);
    }

    /**
     * Tampilkan halaman editor untuk membuat atau mengedit peran.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function editor($id = 0)
    {
        $item = $id ? Role::with('permissions')->findOrFail($id) : new Role();
        $permissions = Permission::all()->toArray();

        return inertia('settings/user-role/Editor', [
            'data' => $item,
            'permissions' => $permissions
        ]);
    }

    /**
     * Simpan peran baru atau perbarui peran yang sudah ada.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $permissions = collect($request->permissions)->map(function ($permission) {
            return is_array($permission) ? $permission['id'] : $permission;
        })->toArray();

        $validated = $request->validate([
            'name'  => [
                'required',
                'max:40',
                Rule::unique('acl_roles', 'name')->ignore($request->id),
            ],
            'description' => 'nullable|string|max:200',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:acl_permissions,id',
        ]);

        try {
            $role = $request->id ? Role::with(['permissions'])->findOrFail($request->id) : new Role();
            $role->name = $validated['name'];
            $role->description = $validated['description'];

            DB::beginTransaction();
            $dirtyAttributes = $role->getDirty();
            $role->save();
            $role->syncPermissions($permissions);
            $role->permissions; // trigger ???

            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Settings,
                    UserActivityLog::Name_UserRole_Create,
                    "Peran pengguna '$role->name' telah ditambahkan.",
                    $role->toArray(),
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Settings,
                    UserActivityLog::Name_UserRole_Update,
                    "Peran pengguna '$role->name' telah diperbarui.",
                    $dirtyAttributes
                );
            }
            DB::commit();

            $message = "Peran pengguna '$role->name' telah berhasil disimpan.";
            return redirect()->route('admin.user-role.index')
                ->with('success', $message);
        } catch (Exception $ex) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $ex->getMessage());
        }
    }

    /**
     * Hapus peran yang sudah ada.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $role = Role::with(['permissions'])->findOrFail($id);
            $roleName = $role->name;
            $role->delete();
            $this->userActivityLogService->log(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UserRole_Delete,
                "Role $role->name telah dihapus.",
            );
            DB::commit();
            return JsonResponseHelper::success($role, "Role '{$roleName}' telah berhasil dihapus.");
        } catch (Exception $ex) {
            DB::rollBack();

            return JsonResponseHelper::error("Terdapat kesalahan saat menghapus role: " . $ex->getMessage(), 500);
        }
    }
}
