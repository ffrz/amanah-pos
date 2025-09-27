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

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DatabaseSettingsController extends Controller
{
    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('settings/database/Index');
    }

    public function logs()
    {
        return inertia('settings/database/Logs');
    }

    public function logDetail()
    {
        return inertia('settings/database/LogDetail');
    }

    public function logData(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = User::with(['roles']);

        // Filter berdasarkan peran dari Spatie
        if (!empty($filter['role']) && $filter['role'] != 'all') {
            $q->whereHas('roles', function ($query) use ($filter) {
                $query->where('id', $filter['role']);
            });
        }

        // Filter berdasarkan status
        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        // Filter berdasarkan pencarian nama atau username
        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('username', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        $users = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($users);
    }

    public function backup() {}

    public function restore() {}

    public function reset() {}
}
