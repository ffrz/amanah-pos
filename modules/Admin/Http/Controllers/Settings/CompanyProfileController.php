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

use App\Helpers\ImageUploaderHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
                'headline' => 'nullable|string|max:200',
                'phone' => 'nullable|string|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
                'address' => 'nullable|string|max:500',
                'logo_path' => 'nullable|max:500',
                'logo_image' => 'nullable|image|max:5120',
            ];

            $validated = $request->validate($rules);

            $existingLogoPath = Setting::value('company.logo_path');

            if ($request->hasFile('logo_image')) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $request->file('logo_image'),
                    'company',
                    $existingLogoPath,
                    240,
                    240
                );
                $validated['logo_path'] = $newlyUploadedImagePath;
            }

            if (empty($validated['logo_path'])) {
                ImageUploaderHelper::deleteImage($existingLogoPath);
            }

            Setting::setValue('company.name', $validated['name']);
            Setting::setValue('company.phone', $validated['phone'] ?? '');
            Setting::setValue('company.address', $validated['address'] ?? '');
            Setting::setValue('company.headline', $validated['headline'] ?? '');
            Setting::setValue('company.logo_path', $validated['logo_path'] ?? '');

            return redirect()->back()->with('success', 'Profil perusahaan berhasil diperbarui.');
        }

        $data = $this->getData();

        return inertia('settings/company-profile/Edit', compact('data'));
    }

    protected function getData()
    {
        return [
            'name' => Setting::value('company.name', env('APP_NAME', 'Amanah POS')),
            'headline' => Setting::value('company.headline', ''),
            'phone' => Setting::value('company.phone', ''),
            'address' => Setting::value('company.address', ''),
            'logo_path' => Setting::value('company.logo_path', null),
        ];
    }
}
