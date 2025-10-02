<?php

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Modules\Admin\Http\Requests\Settings\CompanyProfile\SaveRequest;
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
     * @param SaveRequest $request (Validasi kondisional menangani GET/POST)
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function edit(SaveRequest $request)
    {
        if ($request->isMethod('GET')) {
            $data = $this->service->getCurrentProfileData();
            return inertia('settings/company-profile/Edit', compact('data'));
        }

        $validated = $request->validated();
        $logoFile = $request->file('logo_image');

        try {
            $updated = $this->service->updateProfile($validated, $logoFile);

            if (!$updated) {
                return redirect()->back()
                    ->with('warning', 'Tidak terdeteksi perubahan data.');
            }

            return redirect()->back()
                ->with('success', 'Profil perusahaan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui profil perusahaan. Silakan coba lagi.');
        }
    }
}
