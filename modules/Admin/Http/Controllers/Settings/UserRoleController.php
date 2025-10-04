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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Spatie\Permission\Models\Role;

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
        $this->authorize('viewAny', Role::class);
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
        $this->authorize('viewAny', Role::class);
        $items = $this->userRoleService->getData($request->validated());
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
        $this->authorize('view', $role);
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
        $this->authorize('create', Role::class);
        $item = $this->userRoleService->duplicate($id);
        return inertia('settings/user-role/Editor', [
            'data' => $item,
            'permissions' => $this->commonDataService->getAclPermissions()
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
        $role = $this->userRoleService->findOrCreate($id);
        $this->authorize(!$id ? 'create' : 'update', $role);
        return inertia('settings/user-role/Editor', [
            'data' => $role,
            'permissions' => $this->commonDataService->getAclPermissions()
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

        $role = $this->userRoleService->findOrCreate($request->id);
        $this->authorize(!$role->id ? 'create' : 'update', $role);
        $role = $this->userRoleService->save($role, $request->validated());
        return redirect()->route('admin.user-role.index')
            ->with('success', "Peran pengguna '{$role->name}' telah berhasil disimpan.");
    }

    /**
     * Hapus peran yang sudah ada.
     *
     * @param int $id ID Peran yang akan dihapus.
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {

        $role = $this->userRoleService->find($id);
        $this->authorize('delete', $role);
        $role = $this->userRoleService->delete($role);
        return JsonResponseHelper::success($role, "Role '{$role->name}' telah berhasil dihapus.");
    }
}
