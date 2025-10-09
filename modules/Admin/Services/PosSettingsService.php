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

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\Setting;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class PosSettingsService
{

    public function __construct(protected UserActivityLogService $userActivityLogService) {}

    /**
     * Dapatkan data pengaturan POS saat ini dari database.
     *
     * @return array<string, string>
     */
    public function getCurrentSettingsData(): array
    {
        return [
            'default_payment_mode' => Setting::value('pos.default_payment_mode', 'cash'),
            'default_print_size' => Setting::value('pos.default_print_size', '58mm'),
            'after_payment_action' => Setting::value('pos.after_payment_action', 'print'),
            'foot_note' => Setting::value('pos.foot_note', ''),
        ];
    }

    /**
     * Memperbarui pengaturan POS dan mencatat aktivitas pengguna.
     *
     * @param array $data Data yang telah divalidasi dari request.
     * @param array $oldData Data pengaturan POS sebelum pembaruan.
     * @return void
     */
    public function save(array $data): void
    {
        $oldData = $this->getCurrentSettingsData();

        if ($data == $oldData) {
            throw new ModelNotModifiedException();
        }

        DB::transaction(function () use ($data, $oldData) {
            Setting::setValue('pos.default_payment_mode', $data['default_payment_mode']);
            Setting::setValue('pos.default_print_size', $data['default_print_size']);
            Setting::setValue('pos.foot_note', $data['foot_note'] ?? '');
            Setting::setValue('pos.after_payment_action', $data['after_payment_action']);

            Setting::refreshAll();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UpdatePosSettings,
                'Pengaturan pos telah diperbarui.',
                [
                    'formatter' => 'pos-settings',
                    'new_data'  => $this->getCurrentSettingsData(),
                    'old_data'  => $oldData,
                ]
            );
        });
    }
}
