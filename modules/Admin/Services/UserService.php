<?php

namespace Modules\Admin\Services;

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
    public function __construct(protected UserActivityLogService $userActivityLogService) {}

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

    /**
     * Mengambil data pengguna dengan paginasi dan filter yang kompleks.
     *
     * @param array $validatedData Data request yang telah divalidasi (termasuk filter, sort, dan per_page).
     * @return LengthAwarePaginator
     */
    public function getData(array $data): LengthAwarePaginator
    {
        $orderBy = $data['order_by'] ?? 'name';
        $orderType = $data['order_type'] ?? 'asc';
        $filter = $data['filter'] ?? [];
        $perPage = $data['per_page'] ?? 10;

        $q = User::with(['roles']);

        // Filter berdasarkan peran dari Spatie
        if (!empty($filter['role']) && $filter['role'] != 'all') {
            $q->whereHas('roles', function ($query) use ($filter) {
                $query->where('id', $filter['role']);
            });
        }

        // Filter berdasarkan status
        if (isset($filter['status']) && ($filter['status'] === 'active' || $filter['status'] === 'inactive')) {
            $q->where('active', '=', $filter['status'] === 'active');
        }

        // Filter berdasarkan pencarian nama atau username
        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('username', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        return $q->paginate($perPage)->withQueryString();
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
        $password = $data['password'] ?? null;
        $roles = $data['roles'] ?? [];

        $user = $isNew ? new User() : User::findOrFail($data['id']);

        $oldData = $user->toArray();
        $user->fill($data);

        // Hapus password agar tidak disimpan dari fill(), karena kita hash secara manual
        unset($user->password);

        $dirtyAttributes = $user->getDirty();

        if (!empty($password)) {
            $user->password = Hash::make($password);
            $dirtyAttributes['password'] = '********'; // Catat sebagai dirty untuk log
        }

        if (!$isNew && empty($dirtyAttributes) && empty(array_diff($user->roles->pluck('id')->toArray(), $roles)) && empty(array_diff($roles, $user->roles->pluck('id')->toArray()))) {
            // Tidak ada perubahan di data User dan Role
            throw new Exception("Tidak ada perubahan terdeteksi.", 200);
        }

        DB::beginTransaction();
        try {
            $user->save();

            // Sinkronisasi peran
            if ($user->type === User::Type_SuperUser) {
                $user->syncRoles([]); // Hapus peran untuk SuperUser
            } else {
                $user->syncRoles($roles);
            }

            // Catat log aktivitas
            $user->load('roles'); // Muat ulang roles untuk log
            if ($isNew) {
                $message = "Pengguna '{$user->username}' telah ditambahkan.";
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Create,
                    $message,
                    [
                        'formatter' => 'user',
                        'new_data' => $user->toArray(),
                    ]
                );
            } else {
                $message = "Pengguna '{$user->username}' telah diperbarui.";
                $this->userActivityLogService->log(
                    UserActivityLog::Category_User,
                    UserActivityLog::Name_User_Update,
                    $message,
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
            throw $e; // Lempar ulang exception untuk ditangani Controller
        }
    }

    /**
     * Menghapus pengguna dan mencatat aktivitas.
     *
     * @param int $userId ID pengguna yang akan dihapus.
     * @param int|null $authUserId ID pengguna yang sedang diautentikasi.
     * @return User
     * @throws Exception
     */
    public function delete(int $userId, ?int $authUserId): User
    {
        $user = $this->find($userId);

        if ($user->id === $authUserId) {
            throw new Exception('Tidak dapat menghapus akun sendiri!', 409);
        }

        DB::beginTransaction();
        try {
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

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
