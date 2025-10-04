<?php

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
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
    public function updateProfile(array $validatedData, ?UploadedFile $logoFile)
    {
        $oldData = $this->getCurrentProfileData();
        $existingLogoPath = $oldData['logo_path'];
        $newLogoPath = $validatedData['logo_path'] ?? null;

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
            $newLogoPath = '';
        }

        $validatedData['logo_path'] = $newLogoPath ?? '';

        $newData = [
            'name'      => $validatedData['name'],
            'headline'  => $validatedData['headline'] ?? '',
            'phone'     => $validatedData['phone'] ?? '',
            'address'   => $validatedData['address'] ?? '',
            'logo_path' => $validatedData['logo_path'],
        ];

        if (!$this->checkIfDataChanged($oldData, $newData)) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($newData, $oldData) {
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
        });
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
