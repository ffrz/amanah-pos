<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function _logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('customer')->user();
        // if ($user) {
        //     $user->setLastActivity('Logout');
        // }
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
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'NIS harus diisi.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);

        // basic validations
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // extra validations
        $data = $request->only(['username', 'password']);
        $data['nis'] = $data['username'];
        unset($data['username']);

        if (!Auth::guard('customer')->attempt($data, $request->has('remember'))) {
            $validator->errors()->add('username', 'Username atau password salah!');
        } else if (!Auth::guard('customer')->user()->active) {
            $validator->errors()->add('username', 'Akun anda tidak aktif. Silahkan hubungi administrator!');
            $this->_logout($request);
        } else {
            /** @var \App\Models\Customer $user */
            $user = Auth::guard('customer')->user();
            $user->setLastLogin();
            $user->setLastActivity('Login');
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
