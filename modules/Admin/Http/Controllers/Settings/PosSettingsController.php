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
use App\Models\Setting;
use App\Models\UserActivityLog;
use App\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosSettingsController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return \Inertia\Response
     */
    public function edit(Request $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $rules = [
                'default_payment_mode' => 'required|string',
                'default_print_size' => 'required|string',
                'after_payment_action' => 'required|string',
                'foot_note' => 'nullable|string|max:200',
            ];

            $validated = $request->validate($rules);
            $oldData = $this->getData();

            if ($validated == $oldData) {
                return redirect()->back()->with('success', 'Tidak terdeteksi perubahan data.');
            }

            DB::beginTransaction();

            Setting::setValue('pos.default_payment_mode', $validated['default_payment_mode']);
            Setting::setValue('pos.default_print_size', $validated['default_print_size']);
            Setting::setValue('pos.foot_note', $validated['foot_note'] ?? '');
            Setting::setValue('pos.after_payment_action', $validated['after_payment_action']);

            Setting::refreshAll();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UpdatePosSettings,
                'Pengaturan pos telah diperbarui.',
                [
                    'new_data' => $this->getData(),
                    'old_data' => $oldData,
                ]
            );
            DB::commit();

            return redirect()->back()->with('success', 'Pengaturan POS berhasil diperbarui.');
        }

        $data = $this->getData();

        return inertia('settings/pos/Edit', compact('data'));
    }

    protected function getData()
    {
        return [
            'default_payment_mode' => Setting::value('pos.default_payment_mode', 'cash'),
            'default_print_size' => Setting::value('pos.default_print_size', '58mm'),
            'after_payment_action' => Setting::value('pos.after_payment_action', 'print'),
            'foot_note' => Setting::value('pos.foot_note', ''),
        ];
    }
}
