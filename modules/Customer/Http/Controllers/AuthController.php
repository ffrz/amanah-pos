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

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function _logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function login(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('auth/Login');
        }

        // kode dibawah ini untuk handle post

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'password' => 'required',
        ], [
            'code.required' => 'Username harus diisi.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);

        // basic validations
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // extra validations
        $data = $request->only(['code', 'password']);
        if (!Auth::guard('customer')->attempt($data, $request->has('remember'))) {
            $validator->errors()->add('code', 'Username atau password salah!');
        } else if (!Auth::guard('customer')->user()->active) {
            $validator->errors()->add('code', 'Akun anda tidak aktif. Silahkan hubungi administrator!');
            $this->_logout($request);
        } else {
            /** @var \App\Models\Customer $user */
            $user = Auth::guard('customer')->user();
            $user->setLastLogin();
            $request->session()->regenerate();
            return redirect(route('customer.dashboard'));
        }

        return redirect()->back()->withInput()->withErrors($validator);
    }

    public function logout(Request $request)
    {
        $this->_logout($request);
        return redirect('/')->with('success', 'Anda telah logout.');
    }

    public function forgotPassword(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('auth/ForgotPassword');
        }
    }
}
