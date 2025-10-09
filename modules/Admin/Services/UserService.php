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

use App\Exceptions\ModelNotModifiedException;
use App\Models\User;
use App\Models\UserActivityLog;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UserService mengelola semua logika bisnis terkait manajemen User (CRUD)
 * dan interaksi dengan Roles Spatie.
 */
class UserService
{
    /**
     * @param UserActivityLogService $userActivityLogService Layanan untuk mencatat aktivitas pengguna.
     */
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService
    ) {}

    public function find($id): User
    {
        return User::with(['roles'])->findOrFail($id);
    }

    public function findOrCreate($id): User
    {
        return $id ? $this->find($id) : new User([
            'active' => true,
        ]);
    }

    public function duplicate($id): User
    {
        return $this->find($id)->replicate();
    }

    /**
     * Mengambil data pengguna dengan paginasi dan filter yang kompleks.
     *
     * @param array $options Data request yang telah divalidasi (termasuk filter, sort, dan per_page).
     * @return LengthAwarePaginator
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = User::with(['roles']);

        if (!empty($filter['roles'])) {
            $q->whereHas('roles', function ($query) use ($filter) {
                $query->where(function ($q) use ($filter) {
                    foreach ($filter['roles'] as $role) {
                        $q->orWhere('id', $role);
                    }
                });
            });
        }

        if (isset($filter['status']) && $filter['status'] != 'all') {
            $q->where('active', '=', $filter['status'] === 'active');
        }

        if (isset($filter['type']) && $filter['type'] != 'all') {
            $q->where('type', '=', $filter['type']);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('username', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Menyimpan (membuat atau memperbarui) pengguna dan peran mereka.
     *
     * @param array $validatedData Data pengguna yang telah divalidasi.
     * @param int|null $authUserId ID pengguna yang sedang diautentikasi (untuk pengecekan jangan mengubah diri sendiri).
     * @return User
     * @throws Exception
     */
    public function save(User $user, array $data): User
    {
        $isNew = empty($data['id']);
        $roles = $data['roles'];
        $oldData = $user->toArray();

        $user->fill($data);

        if (empty($user->getDirty())) {
            throw new ModelNotModifiedException();
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $oldData['password'] = '*****';
        }

        return DB::transaction(function () use ($user, $isNew, $roles, $oldData) {
            $user->save();

            if ($user->type === User::Type_SuperUser) {
                $user->syncRoles([]);
            } else {
                $user->syncRoles($roles);
            }

            $user->load('roles');

            $this->documentVersionService->createVersion($user);

            $newData = $user->toArray();

            if (!empty($oldData['password'])) {
                $newData['password'] = '******';
            }

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Create,
                    "Pengguna '{$user->username}' telah ditambahkan.",
                    [
                        'formatter' => 'user',
                        'new_data'  => $newData,
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Update,
                    "Pengguna '{$user->username}' telah diperbarui.",
                    [
                        'formatter' => 'user',
                        'new_data'  => $newData,
                        'old_data'  => $oldData,
                    ]
                );
            }

            return $user;
        });
    }

    /**
     * Menghapus pengguna dan mencatat aktivitas.
     *
     * @param user $user pengguna yang akan dihapus.
     * @return User
     * @throws Exception
     */
    public function delete(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->delete();

            $this->documentVersionService->createDeletedVersion($user);

            $this->userActivityLogService->log(
                UserActivityLog::Category_User,
                UserActivityLog::Name_User_Delete,
                "Pengguna '{$user->username}' telah dihapus.",
                [
                    'formatter' => 'user',
                    'data' => $user->toArray(),
                ]

            );

            return $user;
        });
    }
}
