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
use Modules\Admin\Services\PosSettingsService;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\Settings\PosSettings\SaveRequest;

class PosSettingsController extends Controller
{
    public function __construct(protected PosSettingsService $posSettingsService) {}

    /**
     * Tampilkan halaman indeks pengguna atau proses pembaruan.
     *
     * @param SaveRequest $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function edit(SaveRequest $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $this->posSettingsService->save($request->validated());
            return redirect()->back()->with('success', 'Pengaturan POS berhasil diperbarui.');
        }

        return inertia('settings/pos/Edit', [
            'data' => $this->posSettingsService->getCurrentSettingsData()
        ]);
    }
}
