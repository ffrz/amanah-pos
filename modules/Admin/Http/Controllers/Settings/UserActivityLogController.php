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
use App\Models\UserActivityLog;
use Modules\Admin\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\UserActivityLog\GetDataRequest;
use Modules\Admin\Services\CommonDataService;

class UserActivityLogController extends Controller
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected CommonDataService $commonDataService
    ) {}

    /**
     * Tampilkan halaman indeks log aktifitas pengguna.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('settings/user-activity-log/Index', [
            'activity_categories' => UserActivityLog::Categories,
            'activity_names' => UserActivityLog::Names,
            'users' => $this->commonDataService->getAllUsers(['id', 'name', 'username']),
        ]);
    }

    /**
     * Tampilkan halaman detail pengguna.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function detail($id = 0)
    {
        $item = $this->userActivityLogService->find($id);
        return inertia('settings/user-activity-log/Detail', [
            'data' => $item,
            'formatted_metadata' => $item->formatted_metadata,
        ]);
    }

    /**
     * Dapatkan data pengguna dalam format paginasi, dengan filter peran dari Spatie.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetDataRequest $request)
    {
        $items = $this->userActivityLogService->getData($request->validated());
        return JsonResponseHelper::success($items);
    }

    /**
     * Hapus rekaman log aktifitas pengguna.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $item = $this->userActivityLogService->delete($id);
        return JsonResponseHelper::success($item, "Log aktifitas #$item->id telah dihapus!");
    }

    /**
     * Hapus seluruh log aktifitas pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        $this->userActivityLogService->clear();

        return JsonResponseHelper::success(null, "Semua log aktifitas telah dibersihkan!");
    }
}
