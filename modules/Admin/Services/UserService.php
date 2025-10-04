<?php

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ModelNotModifiedException;
use App\Models\User;
use App\Models\UserActivityLog;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

    public function find($id)
    {
        return User::with(['roles'])->findOrFail($id);
    }

    public function findOrCreate($id)
    {
        return $id ? $this->find($id) : new User([
            'active' => true,
        ]);
    }

    public function duplicate($id)
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

        // Filter berdasarkan peran dari Spatie
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
    public function save(array $data): User
    {
        $isNew = empty($data['id']);
        $roles = $data['roles'];
        $user = $this->findOrCreate($data['id']);
        $oldData = $user->toArray();
        $user->fill($data);

        $dirtyAttributes = $user->getDirty();

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $dirtyAttributes['password'] = '********'; // Catat sebagai dirty untuk log
        }

        if (!$isNew && empty($dirtyAttributes) && empty(array_diff($user->roles->pluck('id')->toArray(), $roles)) && empty(array_diff($roles, $user->roles->pluck('id')->toArray()))) {
            throw new ModelNotModifiedException();
        }

        try {
            DB::beginTransaction();
            $user->save();

            if ($user->type === User::Type_SuperUser) {
                $user->syncRoles([]);
            } else {
                $user->syncRoles($roles);
            }

            $user->load('roles');

            $this->documentVersionService->createVersion($user);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Create,
                    "Pengguna '{$user->username}' telah ditambahkan.",
                    [
                        'formatter' => 'user',
                        'new_data' => $user->toArray(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Update,
                    "Pengguna '{$user->username}' telah diperbarui.",
                    [
                        'formatter' => 'user',
                        'new_data' => $user->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        if ($user->id === Auth::id()) {
            throw new BusinessRuleViolationException('Tidak dapat menghapus akun sendiri!', 409);
        }

        try {
            DB::beginTransaction();

            $oldData = $user->toArray();

            $user->delete();

            // Catat log
            $this->userActivityLogService->log(
                UserActivityLog::Category_User,
                UserActivityLog::Name_User_Delete,
                "Pengguna '{$user->username}' telah dihapus.",
                [
                    'formatter' => 'user',
                    'data' => $oldData,
                ]

            );

            $this->documentVersionService->createDeletedVersion($user);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
