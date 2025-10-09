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
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        // protected DocumentVersionService $documentVersionService
    ) {}

    /**
     * Dapatkan data peran dalam format paginasi.
     *
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function getData(array $options): LengthAwarePaginator
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
        $item = $this->find($id);
        $item->id = null;
        return $item;
    }

    /**
     * Persiapkan data untuk halaman editor peran (buat atau edit).
     *
     * @param int $id
     * @return Role
     */
    public function findOrCreate($id): Role
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
    public function save(Role $role, array $data): Role
    {
        $permissions = collect($data['permissions'] ?? [])->map(function ($permission) {
            return is_array($permission) ? $permission['id'] : $permission;
        })->toArray();

        $oldData = $data['id'] ? $role->toArray() : null;

        $role->fill($data);

        return DB::transaction(function () use ($role, $oldData, $permissions) {
            $isNew = !$role->id;

            $role->save();
            $role->syncPermissions($permissions);
            $role->load('permissions');

            // $this->documentVersionService->createVersion($role);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_UserRole,
                    UserActivityLog::Name_UserRole_Create,
                    "Peran pengguna '{$role->name}' telah ditambahkan.",
                    [
                        'formatter' => 'user-role',
                        'new_data' => $role->toArray(),
                    ],
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_UserRole,
                    UserActivityLog::Name_UserRole_Update,
                    "Peran pengguna '{$role->name}' telah diperbarui.",
                    [
                        'formatter' => 'user-role',
                        'new_data' => $role->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            return $role;
        });
    }

    /**
     * Menghapus peran yang sudah ada.
     *
     * @param int $id ID peran yang akan dihapus.
     * @return Role
     */
    public function delete(Role $role): Role
    {
        return DB::transaction(function () use ($role) {
            $role->delete();

            // $this->documentVersionService->createDeletedVersion($role);

            $this->userActivityLogService->log(
                UserActivityLog::Category_UserRole,
                UserActivityLog::Name_UserRole_Delete,
                "Role $role->name telah dihapus.",
                [
                    'formatter' => 'user-role',
                    'data' => $role->toArray(),
                ]
            );

            return $role;
        });
    }
}
