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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User; // Pastikan model User Anda diimport

class AuthController extends Controller
{
    /**
     * Menangani proses login API dan mengeluarkan API Token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // 1. Validasi input dasar menggunakan username
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'ID Pengguna harus diisi.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);

        // Ambil kredensial dari request
        $credentials = $request->only('username', 'password');

        // 2. Coba proses autentikasi menggunakan Auth::attempt
        // Secara default, Auth::attempt akan mencari kolom 'email' jika tidak diubah.
        // Jika Anda menggunakan 'username' sebagai kolom login, pastikan ini dikonfigurasi
        // di model User Anda dengan menambahkan public static function username() atau di config/auth.php.
        // Asumsi: 'username' adalah kolom yang digunakan untuk login.
        if (! Auth::attempt($credentials)) {
            // Jika autentikasi gagal (username/password salah)
            throw ValidationException::withMessages([
                'username' => ['ID Pengguna atau kata sandi salah!'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Dapatkan instance user yang terautentikasi

        // 3. Validasi status aktif user
        if (! $user->active) {
            // Jika akun tidak aktif, logout user dari sesi (jika ada) dan lempar error
            Auth::logout(); // Logout dari guard default (biasanya 'web')
            if ($request->session()->isValid()) { // Pastikan sesi valid sebelum invalidasi
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            throw ValidationException::withMessages([
                'username' => ['Akun anda tidak aktif. Silahkan hubungi administrator!'],
            ]);
        }

        // 4. Autentikasi berhasil dan user aktif
        // Hapus token lama jika ada (opsional, untuk keamanan)
        // Ini akan menghapus semua token yang dikeluarkan user ini.
        $user->tokens()->delete();

        // Buat token baru dengan nama 'api-token'
        $token = $user->createToken('api-token')->plainTextToken;

        // Perbarui status login dan aktifitas user (jika metode ini ada di model User)
        // Ini adalah metode kustom Anda, jadi pastikan tersedia di App\Models\User.
        if (method_exists($user, 'setLastLogin')) {
            $user->setLastLogin();
        }
        if (method_exists($user, 'setLastActivity')) {
            $user->setLastActivity('API Login'); // Atau 'Login'
        }

        // Kembalikan respons JSON dengan token
        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $user->toArray(), // Kembalikan data user sebagai array
        ]);
    }

    /**
     * Menangani proses logout API (menghapus API Token).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Menghapus token API yang sedang digunakan
        // Pastikan request sudah melewati middleware 'auth:sanctum'
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();

            // Opsional: Mencatat aktifitas logout API
            if (method_exists($request->user(), 'setLastActivity')) {
                $request->user()->setLastActivity('API Logout');
            }
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
