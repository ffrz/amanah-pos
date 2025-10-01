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


namespace Modules\Admin\Services;

use App\Models\UserActivityLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRoleService
{
    public function __construct(protected UserActivityLogService $userActivityLogService) {}

    /**
     * Dapatkan data peran dalam format paginasi.
     *
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function getPaginatedData(array $options): LengthAwarePaginator
    {
        $q = Role::query();

        if (!empty($options['filter']['search'])) {
            $q->where('name', 'like', '%' . $options['filter']['search'] . '%');
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Dapatkan detail peran beserta permissions dan users.
     *
     * @param int $id
     * @return Role
     */
    public function find(int $id): Role
    {
        return Role::with(['permissions', 'users'])->findOrFail($id);
    }

    /**
     * Persiapkan data untuk halaman duplikasi peran.
     *
     * @param int $id
     * @return Role
     */
    public function duplicate(int $id): Role
    {
        $item = Role::with('permissions')->findOrFail($id);
        $item->id = null;
        return $item;
    }

    /**
     * Persiapkan data untuk halaman editor peran (buat atau edit).
     *
     * @param int $id
     * @return Role
     */
    public function findOrCreate(int $id): Role
    {
        return $id ? Role::with('permissions')->findOrFail($id) : new Role();
    }

    /**
     * Menyimpan (membuat atau memperbarui) peran dan mensinkronisasi permissions.
     *
     * @param array $validatedData Data yang sudah divalidasi dari request.
     * @param int|null $roleId ID peran, null jika membuat baru.
     * @return Role
     */
    public function save(array $data): Role
    {
        $permissions = collect($data['permissions'] ?? [])->map(function ($permission) {
            return is_array($permission) ? $permission['id'] : $permission;
        })->toArray();

        $isUpdate = (bool)$data['id'];

        DB::beginTransaction();
        try {
            $role = $isUpdate ? Role::with(['permissions'])->findOrFail($data['id']) : new Role();
            $oldData = $isUpdate ? $role->toArray() : null;

            $role->name = $data['name'];
            $role->description = $data['description'] ?? null;

            $role->save();
            $role->syncPermissions($permissions);

            if (!$isUpdate) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Settings,
                    UserActivityLog::Name_UserRole_Create,
                    "Peran pengguna '{$role->name}' telah ditambahkan.",
                    $role->toArray(),
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Settings,
                    UserActivityLog::Name_UserRole_Update,
                    "Peran pengguna '{$role->name}' telah diperbarui.",
                    [
                        'new_data' => $role->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Menghapus peran yang sudah ada.
     *
     * @param int $id ID peran yang akan dihapus.
     * @return Role
     */
    public function delete(int $id): Role
    {
        DB::beginTransaction();
        try {
            $role = Role::with(['permissions'])->findOrFail($id);
            $roleName = $role->name;

            $role->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UserRole_Delete,
                "Role $roleName telah dihapus.",
                $role->toArray(),
            );

            DB::commit();

            $role->name = $roleName;
            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
