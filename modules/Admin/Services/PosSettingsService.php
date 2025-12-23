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
use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            'default_payment_mode' => $user->getSetting('pos.default_payment_mode', 'cash'),
            'default_print_size' => $user->getSetting('pos.default_print_size', '58mm'),
            'after_payment_action' => $user->getSetting('pos.after_payment_action', 'print'),

            // Global setting
            'allow_negative_inventory' => Setting::value('pos.allow_negative_inventory', false),
            'allow_credit_limit' => Setting::value('pos.allow_credit_limit', false),
            'allow_selling_at_loss' => Setting::value('pos.allow_selling_at_loss', false),
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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        DB::transaction(function () use ($data, $oldData, $user) {
            $user->setSetting('pos.default_payment_mode', $data['default_payment_mode']);
            $user->setSetting('pos.default_print_size', $data['default_print_size']);
            $user->setSetting('pos.after_payment_action', $data['after_payment_action']);

            Setting::setValue('pos.allow_negative_inventory', $data['allow_negative_inventory'] ?? false);
            Setting::setValue('pos.allow_credit_limit', $data['allow_credit_limit'] ?? false);
            Setting::setValue('pos.allow_selling_at_loss', $data['allow_selling_at_loss'] ?? false);
            Setting::setValue('pos.foot_note', $data['foot_note'] ?? '');
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
