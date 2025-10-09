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

use App\Http\Controllers\Controller;
use Modules\Admin\Services\UserProfileService;
use Modules\Admin\Http\Requests\User\UpdatePasswordRequest;
use Modules\Admin\Http\Requests\User\UpdateProfileRequest;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola tampilan dan pembaruan profil pengguna yang sedang diautentikasi.
 * Mendelegasikan semua logika bisnis dan transaksi ke UserProfileService.
 */
class UserProfileController extends Controller
{
    /**
     * Menggunakan Dependency Injection untuk UserProfileService.
     *
     * @param UserProfileService $userProfileService
     */
    public function __construct(protected UserProfileService $userProfileService) {}

    /**
     * Menampilkan formulir profil pengguna.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/user-profile/Edit');
    }

    /**
     * Memperbarui informasi profil pengguna.
     *
     * Controller hanya menerima Request (setelah divalidasi oleh Form Request) dan mendelegasikan ke Service.
     *
     * @param UpdateProfileRequest $request (Gunakan Form Request untuk validasi)
     * @return RedirectResponse
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userProfileService->updateProfile(Auth::user(), $request->validated());
        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Memperbarui kata sandi pengguna.
     *
     * Controller hanya menerima Request (setelah divalidasi) dan mendelegasikan ke Service.
     *
     * @param UpdatePasswordRequest $request (Gunakan Form Request untuk validasi)
     * @return RedirectResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $this->userProfileService->updatePassword(
                Auth::user(),
                $request->input('current_password'),
                $request->input('password')
            );
            return back()->with('success', 'Kata sandi berhasil diperbarui');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }
}
