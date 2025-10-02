<?php

namespace Modules\Admin\Services;

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
     * @param array $validatedData Data yang telah divalidasi dari request.
     * @param array $oldData Data pengaturan POS sebelum pembaruan.
     * @return void
     */
    public function save(array $validatedData, array $oldData): void
    {
        DB::beginTransaction();

        try {
            Setting::setValue('pos.default_payment_mode', $validatedData['default_payment_mode']);
            Setting::setValue('pos.default_print_size', $validatedData['default_print_size']);
            Setting::setValue('pos.foot_note', $validatedData['foot_note'] ?? '');
            Setting::setValue('pos.after_payment_action', $validatedData['after_payment_action']);

            Setting::refreshAll();

            // Log aktivitas
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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Penting: Service harus melempar exception agar controller tahu bahwa operasi gagal
            throw new \Exception('Gagal memperbarui pengaturan POS: ' . $e->getMessage());
        }
    }
}
