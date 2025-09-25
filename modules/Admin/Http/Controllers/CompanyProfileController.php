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

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
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
     * Memperbarui informasi profil perusahaan.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
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

            Auth::user()->setLastActivity('Memperbarui profil perusahaan');

            return JsonResponseHelper::success($this->getData(), 'Profil perusahaan berhasil diperbarui.');
        }

        $data = $this->getData();

        return inertia('company-profile/Edit', compact('data'));
    }

    protected function getData()
    {
        return [
            'name' => Setting::value('company_name', env('APP_NAME', 'Koperasiku')),
            'phone' => Setting::value('company_phone', ''),
            'address' => Setting::value('company_address', ''),
        ];
    }
}
