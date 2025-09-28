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

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use App\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
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
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('user-profile/Edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:100',
        ]);

        /** @var \App\Models\User */
        $user = Auth::user();
        $originalAttributes = $user->getOriginal();
        $user->fill($validated);
        $dirtyAttributes = $user->getDirty();

        if (empty($dirtyAttributes)) {
            return back()->with('warning', 'Tidak ada perubahan yang terdeteksi');
        }

        $metadata = [];
        foreach (array_keys($dirtyAttributes) as $key) {
            $metadata[$key] = [
                'old' => $originalAttributes[$key] ?? null,
                'new' => $dirtyAttributes[$key],
            ];
        }

        DB::beginTransaction();
        $user->save();
        $this->userActivityLogService->log(
            UserActivityLog::Category_UserProfile,
            UserActivityLog::Name_UserProfile_UpdateProfile,
            'Data profil telah diperbarui.',
            $metadata
        );
        DB::commit();

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => 'required|confirmed|min:5',
        ]);

        $user = $request->user();

        if (! $user || ! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        DB::beginTransaction();
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);
        $this->userActivityLogService->log(
            UserActivityLog::Category_UserProfile,
            UserActivityLog::Name_UserProfile_ChangePassword,
            'Kata sandi telah diperbarui.'
        );
        DB::commit();

        return back()->with('success', 'Password berhasil diperbarui');
    }
}
