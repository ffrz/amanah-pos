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

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserActivityLogController extends Controller
{
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
        return inertia('settings/user-activity-log/Detail', [
            'data' => UserActivityLog::with('user')->findOrFail($id),
        ]);
    }

    /**
     * Dapatkan data pengguna dalam format paginasi, dengan filter peran dari Spatie.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = UserActivityLog::query();

        if (!empty($filter['user_id']) && $filter['user_id'] != 'all') {
            $q->where('user_id', $filter['user_id']);
        }

        if (!empty($filter['activity_category']) && $filter['activity_category'] != 'all') {
            $q->where('activity_category', $filter['activity_category']);
        }

        if (!empty($filter['activity_name']) && $filter['activity_name'] != 'all') {
            $q->where('activity_name', $filter['activity_name']);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('description', 'like', '%' . $filter['search'] . '%');
                $query->where('username', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);
        $q->select(['id', 'logged_at', 'activity_name', 'activity_category', 'description', 'user_id', 'username']);
        $users = $q->paginate($request->get('per_page', 10))->withQueryString();

        return JsonResponseHelper::success($users);
    }

    /**
     * Hapus rekaman log aktifitas pengguna.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $item = UserActivityLog::findOrFail($id);
        $item->delete();
        return JsonResponseHelper::success($item, "Log aktifitas #$item->id telah dihapus!");
    }

    /**
     * Hapus seluruh log aktifitas pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            UserActivityLog::truncate();
            return JsonResponseHelper::success(null, "Semua log aktifitas telah dibersihkan!");
        } catch (\Exception $e) {
            return JsonResponseHelper::error("Gagal membersihkan log: " . $e->getMessage(), 500);
        }
    }
}
