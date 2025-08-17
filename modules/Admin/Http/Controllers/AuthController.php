<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // Catat aktivitas logout jika user masih terautentikasi
        if ($user) {
            $user->setLastActivity('Logout');
        }

        // Lakukan proses logout standar Laravel
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
        // Tampilkan form login jika metode request adalah GET
        if ($request->isMethod('GET')) {
            return inertia('auth/Login', [
                'data' => [
                    // Isi username/password untuk demo jika APP_DEMO true
                    'username' => env('APP_DEMO') ? 'admin' : '',
                    'password' => env('APP_DEMO') ? '12345' : '',
                ]
            ]);
        }

        // --- Handle POST request (proses login) ---

        // 1. Validasi input dasar
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'], // Tambahkan 'string' untuk kejelasan
            'password' => ['required', 'string'], // Tambahkan 'string' untuk kejelasan
        ], [
            'username.required' => 'ID Pengguna harus diisi.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);

        // Jika validasi dasar gagal, kembalikan dengan error dan input sebelumnya
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Ambil kredensial dari request
        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember'); // Gunakan boolean() untuk nilai boolean

        // 2. Coba proses autentikasi
        if (!Auth::attempt($credentials, $remember)) {
            // Jika autentikasi gagal (username/password salah)
            // Lempar ValidationException untuk menampilkan error pada field 'username'
            throw ValidationException::withMessages([
                'username' => ['ID Pengguna atau kata sandi salah!'],
            ])->redirectTo(url()->previous()); // Redirect kembali ke halaman sebelumnya
        }

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Dapatkan instance user yang terautentikasi

        // 3. Validasi status aktif user
        if (!$user->active) {
            // Jika akun tidak aktif, logout user dan lempar error
            $this->_logout($request); // Pastikan user logout
            throw ValidationException::withMessages([
                'username' => ['Akun anda tidak aktif. Silahkan hubungi administrator!'],
            ])->redirectTo(url()->previous()); // Redirect kembali ke halaman sebelumnya
        }

        // 4. Autentikasi berhasil dan user aktif
        // Perbarui status login dan aktivitas user
        $user->setLastLogin();
        $user->setLastActivity('Login');

        // Regenerate session ID untuk keamanan (mengurangi risiko session fixation)
        $request->session()->regenerate();

        // Redirect ke dashboard admin
        return redirect()->intended(route('admin.dashboard')); // Gunakan intended() untuk redirect ke URL yang dituju sebelumnya
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

        // Jika logika POST untuk forgot password akan diimplementasikan, tambahkan di sini
    }
}
