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

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;

use Modules\Admin\Http\Requests\UserRole\GetDataRequest;
use Modules\Admin\Http\Requests\UserRole\SaveRequest;
use Modules\Admin\Services\UserRoleService;
use Modules\Admin\Services\CommonDataService;

use Exception;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

/**
 * Controller untuk mengelola Peran Pengguna (User Roles) dalam pengaturan (Settings).
 * Menerima Request, mendelegasikan operasi ke Service, dan mengembalikan Response Inertia atau JSON.
 */
class UserRoleController extends Controller
{
    /**
     * Inisialisasi UserRoleService untuk logika CRUD dan CommonDataService untuk data umum.
     * * @param UserRoleService $userRoleService
     * @param CommonDataService $commonDataService
     */
    public function __construct(
        protected UserRoleService $userRoleService,
        protected CommonDataService $commonDataService
    ) {}

    /**
     * Tampilkan halaman indeks peran.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('settings/user-role/Index');
    }

    /**
     * Dapatkan data peran dalam format paginasi.
     * Menggunakan GetDataRequest untuk validasi input paginasi.
     *
     * @param GetDataRequest $request
     * @return JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $items = $this->userRoleService->getPaginatedData($request->validated());

        return JsonResponseHelper::success($items);
    }


    /**
     * Tampilkan detail peran.
     *
     * @param int $id ID Peran yang akan ditampilkan.
     * @return Response
     */
    public function detail(int $id): Response
    {
        $role = $this->userRoleService->find($id);

        return inertia('settings/user-role/Detail', [
            'data' => $role,
        ]);
    }

    /**
     * Tampilkan halaman editor untuk duplikasi peran.
     *
     * @param int $id ID Peran sumber yang akan diduplikasi.
     * @return Response
     */
    public function duplicate(int $id): Response
    {
        $item = $this->userRoleService->duplicate($id);

        $permissions = $this->commonDataService->getAclPermissions();

        return inertia('settings/user-role/Editor', [
            'data' => $item,
            'permissions' => $permissions
        ]);
    }

    /**
     * Tampilkan halaman editor untuk membuat atau mengedit peran.
     *
     * @param int $id ID Peran yang akan diedit, default 0 jika membuat baru.
     * @return Response
     */
    public function editor(int $id = 0): Response
    {
        $item = $this->userRoleService->findOrCreate($id);

        $permissions = $this->commonDataService->getAclPermissions();

        return inertia('settings/user-role/Editor', [
            'data' => $item,
            'permissions' => $permissions
        ]);
    }

    /**
     * Simpan peran baru atau perbarui peran yang sudah ada.
     * Menggunakan SaveRequest untuk validasi input.
     *
     * @param SaveRequest $request
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        try {
            $role = $this->userRoleService->save($request->validated());
            $message = "Peran pengguna '{$role->name}' telah berhasil disimpan.";
            return redirect()->route('admin.user-role.index')
                ->with('success', $message);
        } catch (Exception $ex) {
            Log::error("Gagal menyimpan atau memperbarui role. ID: " . ($request->id ?? 'new') . ": " . $ex->getMessage(), ['exception' => $ex]);
            return back()->with('error', 'Terjadi kesalahan: ' . $ex->getMessage());
        }
    }

    /**
     * Hapus peran yang sudah ada.
     *
     * @param int $id ID Peran yang akan dihapus.
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $role = $this->userRoleService->delete($id);
            return JsonResponseHelper::success($role, "Role '{$role->name}' telah berhasil dihapus.");
        } catch (Exception $ex) {
            Log::error("Gagal menghapus role. ID: $id. Exception: " . $ex->getMessage(), ['exception' => $ex]);
            return JsonResponseHelper::error("Gagal menghapus role: " . $ex->getMessage(), 500);
        }
    }
}
