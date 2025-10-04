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
use App\Models\User;
use Modules\Admin\Services\UserService;
use Modules\Admin\Http\Requests\User\GetDataRequest;
use Modules\Admin\Http\Requests\User\SaveRequest;
use Modules\Admin\Services\CommonDataService;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Inject UserService yang sudah memiliki UserActivityLogService di dalamnya.
     */
    public function __construct(
        protected UserService $userService,
        protected CommonDataService $commonDataService
    ) {}

    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);
        return inertia('settings/user/Index', [
            'roles' => $this->commonDataService->getRoles()
        ]);
    }

    /**
     * Tampilkan halaman detail pengguna.
     *
     * @param int $id
     * @return Response
     */
    public function detail(int $id): Response
    {
        $user = $this->userService->find($id);
        $this->authorize('view', $user);
        return inertia('settings/user/Detail', [
            'data' => $user,
        ]);
    }

    /**
     * Dapatkan data pengguna dalam format paginasi, dengan filter peran dari Spatie.
     *
     * Menggunakan GetDataRequest untuk validasi.
     *
     * @param GetDataRequest $request
     * @return JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = $this->userService->getData($request->validated());
        return JsonResponseHelper::success($users);
    }

    /**
     * Duplikasi pengguna.
     *
     * @param int $id
     * @return Response
     */
    public function duplicate(int $id): Response
    {
        $this->authorize('create', User::class);
        $user = $this->userService->duplicate($id);
        return inertia('settings/user/Editor', [
            'data' => $user,
            'roles' => $this->commonDataService->getRoles()
        ]);
    }

    /**
     * Tampilkan halaman editor untuk pengguna (Create/Edit).
     *
     * @param int $id
     */
    public function editor(int $id = 0)
    {
        $user = $this->userService->findOrCreate($id);
        $this->authorize(!$user->id ? 'create' : 'update', $user);
        return inertia('settings/user/Editor', [
            'data' => $user,
            'roles' => $this->commonDataService->getRoles(),
        ]);
    }

    /**
     * Simpan pengguna baru atau perbarui pengguna yang sudah ada, termasuk peran.
     *
     * Menggunakan SaveRequest untuk validasi.
     *
     * @param SaveRequest $request
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $user = $this->userService->findOrCreate($request->id);
        $this->authorize(!$user->id ? 'create' : 'update', $user);
        $user = $this->userService->save($user, $request->validated());
        return redirect(route('admin.user.index'))
            ->with('success', "Pengguna $user->username telah disimpan.");
    }

    /**
     * Hapus pengguna.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $user = $this->userService->find($id);
        $this->authorize('delete', $user);
        $user = $this->userService->delete($user);
        return JsonResponseHelper::success($user, "Pengguna {$user->username} telah dihapus!");
    }
}
