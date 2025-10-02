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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            $validated = $request->validated();

            $oldData = $this->posSettingsService->getCurrentSettingsData();

            if ($validated == $oldData) {
                return redirect()->back()
                    ->with('warning', 'Tidak terdeteksi perubahan data.');
            }

            try {
                $this->posSettingsService->save($validated, $oldData);
                return redirect()->back()->with('success', 'Pengaturan POS berhasil diperbarui.');
            } catch (\Exception $e) {
                Log::error("Gagal memperbarui pengaturan POS.");
                return redirect()->back()->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        return inertia('settings/pos/Edit', [
            'data' => $this->posSettingsService->getCurrentSettingsData()
        ]);
    }
}
