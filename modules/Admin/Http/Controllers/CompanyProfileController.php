<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CompanyProfileController
 *
 * Controller ini bertanggung jawab untuk mengelola profil perusahaan,
 * termasuk menampilkan dan memperbarui informasi seperti nama, telepon, dan alamat perusahaan.
 * Akses ke controller ini dibatasi hanya untuk peran admin.
 */
class CompanyProfileController extends Controller
{
    /**
     * Konstruktor untuk CompanyProfileController.
     * Menerapkan middleware untuk otorisasi peran.
     */
    public function __construct()
    {
        allowed_roles(User::Role_Admin);
    }

    /**
     * Menampilkan form profil perusahaan.
     *
     * @return \Inertia\Response
     */
    public function edit()
    {
        $data = [
            'name' => Setting::value('company_name', env('APP_NAME', 'Koperasiku')),
            'phone' => Setting::value('company_phone', ''),
            'address' => Setting::value('company_address', ''),
        ];

        return inertia('company-profile/Edit', compact('data'));
    }

    /**
     * Memperbarui informasi profil perusahaan.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::user()->setLastActivity('Memperbarui profil perusahaan');

        $rules = [
            'name' => 'required|string|min:2|max:100',
            'phone' => 'nullable|string|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address' => 'nullable|string|max:1000',
        ];

        $validatedData = $request->validate($rules);

        $name = $validatedData['name'];
        $phone = $validatedData['phone'] ?? '';
        $address = $validatedData['address'] ?? '';

        Setting::setValue('company_name', $name);
        Setting::setValue('company_phone', $phone);
        Setting::setValue('company_address', $address);

        return back()->with('success', 'Profil koperasi berhasil diperbarui.');
    }
}
