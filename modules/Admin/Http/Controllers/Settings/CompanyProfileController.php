<?php

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
