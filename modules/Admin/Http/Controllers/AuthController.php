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
use App\Models\Setting;
use App\Models\User;
use App\Models\UserActivityLog;
use Modules\Admin\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthController
 *
 * Controller ini menangani semua proses otentikasi untuk area admin,
 * termasuk menampilkan form login, memproses login, dan logout.
 * Ini juga menyediakan placeholder untuk fitur lupa password.
 */
class AuthController extends Controller
{
    protected $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    /**
     * Menangani proses logout pengguna.
     *
     * @param Request $request
     * @return void
     */
    private function _logout(Request $request): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Auth,
                UserActivityLog::Name_Auth_Logout,
                "Pengguna $user->username telah logout."
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Menampilkan form login atau memproses permintaan login.
     *
     * @param Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        if ($request->isMethod('GET')) {
            return inertia('auth/Login', [
                'data' => [
                    'username' => env('APP_DEMO') ? 'admin' : '',
                    'password' => env('APP_DEMO') ? '12345' : '',
                ],
                'company' => [
                    'name' => Setting::value('company.name', 'Amanah Mart'),
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'ID Pengguna harus diisi.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'username' => ['ID Pengguna atau kata sandi salah!'],
            ])->redirectTo(url()->previous());
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->active) {
            $this->_logout($request);
            throw ValidationException::withMessages([
                'username' => ['Akun anda tidak aktif. Silahkan hubungi administrator!'],
            ])->redirectTo(url()->previous());
        }

        $user->setLastLogin();

        $this->userActivityLogService->log(
            UserActivityLog::Category_Auth,
            UserActivityLog::Name_Auth_Login,
            "Pengguna $user->username telah login."
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.home'));
    }

    /**
     * Menangani proses logout dan redirect.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $this->_logout($request);

        $url = $user instanceof User ? 'admin.auth.login' : 'customer.auth.login';
        return redirect(route($url))->with('success', 'Anda telah logout.');
    }

    /**
     * Menampilkan form lupa password (placeholder).
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function forgotPassword(Request $request)
    {
        if ($request->isMethod('GET')) {
            return inertia('auth/ForgotPassword');
        }
    }
}
