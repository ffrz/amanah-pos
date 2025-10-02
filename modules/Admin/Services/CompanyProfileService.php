<?php

namespace Modules\Admin\Services;

use App\Helpers\ImageUploaderHelper;
use App\Models\Setting;
use App\Models\UserActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CompanyProfileService
{
    public function __construct(protected UserActivityLogService $userActivityLogService) {}

    /**
     * Mengambil data profil perusahaan saat ini.
     */
    public function getCurrentProfileData(): array
    {
        return [
            'name'      => Setting::value('company.name', env('APP_NAME', 'Amanah POS')),
            'headline'  => Setting::value('company.headline', ''),
            'phone'     => Setting::value('company.phone', ''),
            'address'   => Setting::value('company.address', ''),
            'logo_path' => Setting::value('company.logo_path', null),
        ];
    }

    /**
     * Memperbarui data profil perusahaan dan menangani upload/hapus logo.
     *
     * @param array $validatedData Data input yang sudah divalidasi.
     * @param UploadedFile|null $logoFile File logo baru yang diunggah.
     * @return bool
     */
    public function updateProfile(array $validatedData, ?UploadedFile $logoFile): bool
    {
        $oldData = $this->getCurrentProfileData();
        $existingLogoPath = $oldData['logo_path'];
        $newLogoPath = $validatedData['logo_path'] ?? null;

        // 1. Penanganan Logo
        if ($logoFile) {
            $newLogoPath = ImageUploaderHelper::uploadAndResize(
                $logoFile,
                'company',
                $existingLogoPath,
                240,
                240
            );
        } elseif (empty($newLogoPath) && $existingLogoPath) {
            // Jika logo_path kosong (dihapus oleh user) dan sebelumnya ada logo
            ImageUploaderHelper::deleteImage($existingLogoPath);
            $newLogoPath = ''; // Set path ke string kosong
        }

        $validatedData['logo_path'] = $newLogoPath ?? '';

        // 2. Data yang Akan Disimpan
        $newData = [
            'name'      => $validatedData['name'],
            'headline'  => $validatedData['headline'] ?? '',
            'phone'     => $validatedData['phone'] ?? '',
            'address'   => $validatedData['address'] ?? '',
            'logo_path' => $validatedData['logo_path'],
        ];

        // 3. Cek Perbedaan (Logika Bisnis)
        $isDifferent = $this->checkIfDataChanged($oldData, $newData);

        if (!$isDifferent) {
            return false; // Tidak ada perubahan
        }

        // 4. Transaksi DB dan Penyimpanan
        DB::beginTransaction();
        try {
            Setting::setValue('company.name', $newData['name']);
            Setting::setValue('company.phone', $newData['phone']);
            Setting::setValue('company.address', $newData['address']);
            Setting::setValue('company.headline', $newData['headline']);
            Setting::setValue('company.logo_path', $newData['logo_path']);

            Setting::refreshAll();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UpdateCompanyProfile,
                'Pengaturan profil perusahaan telah diperbarui.',
                [
                    'formatter' => 'company-profile',
                    'new_data'  => $newData,
                    'old_data'  => $oldData
                ]
            );

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Membandingkan data lama dan baru untuk menentukan apakah ada perubahan.
     */
    protected function checkIfDataChanged(array $oldData, array $newData): bool
    {
        $oldComparison = array_diff_key($oldData, ['logo_path' => null]);
        $newComparison = array_diff_key($newData, ['logo_path' => null]);

        $diffScalar = !empty(array_diff($oldComparison, $newComparison));
        $diffLogo = $oldData['logo_path'] !== $newData['logo_path'];

        return $diffScalar || $diffLogo;
    }
}
