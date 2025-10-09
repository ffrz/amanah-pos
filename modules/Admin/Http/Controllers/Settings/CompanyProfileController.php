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
use App\Models\UserActivityLog;
use Modules\Admin\Http\Requests\Settings\CompanyProfile\EditorRequest;
use Modules\Admin\Services\CompanyProfileService;

/**
 * Class CompanyProfileController
 */
class CompanyProfileController extends Controller
{
    public function __construct(protected CompanyProfileService $service) {}

    /**
     * Mengelola tampilan dan pembaruan profil perusahaan (GET/POST).
     *
     * @param EditorRequest $request (Validasi kondisional menangani GET/POST)
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function edit(EditorRequest $request)
    {
        if ($request->isMethod('GET')) {
            $data = $this->service->getCurrentProfileData();
            return inertia('settings/company-profile/Edit', [
                'data' => $data
            ]);
        }

        $this->service->updateProfile($request->validated(), $request->file('logo_image'));

        return redirect()->back()
            ->with('success', 'Profil perusahaan berhasil diperbarui.');
    }

    public function delete($id): UserActivityLog
    {
        $item = $this->find($id);
        $item->delete();
        return $item;
    }

    public function find($id): UserActivityLog
    {
        return UserActivityLog::findOrFail($id);
    }
}
