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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * UserProfileService mengelola semua logika bisnis terkait pembaruan profil
 * dan kata sandi pengguna.
 */
class UserProfileService
{
    /**
     * @param UserActivityLogService $userActivityLogService Layanan untuk mencatat aktivitas pengguna.
     */
    public function __construct(protected UserActivityLogService $userActivityLogService) {}

    /**
     * Memperbarui informasi profil pengguna dan mencatat aktivitas.
     *
     * @param array $validatedData Data profil yang telah divalidasi (hanya 'name').
     * @return bool|User
     * @throws InvalidArgumentException Jika tidak ada perubahan yang terdeteksi.
     * @throws Exception Jika terjadi kegagalan saat menyimpan atau transaksi database.
     */
    public function updateProfile(User $user, array $data): User
    {
        $oldData = $user->toArray();

        $user->fill($data);

        if (empty($user->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($user, $oldData) {
            $user->save();

            $this->userActivityLogService->log(
                UserActivityLog::Category_UserProfile,
                UserActivityLog::Name_UserProfile_UpdateProfile,
                'Data profil telah diperbarui.',
                [
                    'formatter' => 'user-profile',
                    'new_data'  => $user->toArray(),
                    'old_data'  => $oldData,
                ]
            );

            return $user;
        });
    }

    /**
     * Memperbarui kata sandi pengguna.
     *
     * @param User $user Instance pengguna yang sedang diautentikasi.
     * @param string $currentPassword Kata sandi pengguna saat ini.
     * @param string $newPassword Kata sandi baru yang telah divalidasi.
     * @return User
     * @throws InvalidArgumentException Jika password saat ini salah.
     * @throws Exception Jika gagal dalam transaksi database.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): User
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new InvalidArgumentException('Password saat ini salah.');
        }

        return DB::transaction(function () use ($newPassword, $user) {
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            $this->userActivityLogService->log(
                UserActivityLog::Category_UserProfile,
                UserActivityLog::Name_UserProfile_ChangePassword,
                'Kata sandi telah diperbarui.'
            );

            return $user;
        });
    }
}
