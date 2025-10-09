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

namespace Modules\Admin\Services;

use App\Models\UserActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UserActivityLogService
{
    /**
     * Mencatat aktifitas pengguna dengan otomatis mengisi konteks lingkungan.
     *
     * @param string $category Menggunakan UserActivityLog::Category_...
     * @param string $name Menggunakan UserActivityLog::Name_...
     * @param string $description Deskripsi ringkas aktifitas.
     * @param array $metadata Data tambahan yang kompleks (standarnya gunakan data baru, data yang dihapus, data sebelum update + dirty attributes).
     * @return UserActivityLog
     */
    public function log(
        string $category,
        string $name,
        string $description,
        array $metadata = [],
    ): UserActivityLog {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->setLastActivity("$category > $name");
        }

        return UserActivityLog::create([
            'activity_category' => $category,
            'activity_name' => $name,
            'description' => $description,
            'metadata' => $metadata,
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->username : '',
            'logged_at' => Carbon::now(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    public function find($id): UserActivityLog
    {
        return UserActivityLog::with('user')->findOrFail($id);
    }

    public function clear()
    {
        $user = Auth::user();

        UserActivityLog::truncate();

        $this->log(
            UserActivityLog::Category_UserActivityLog,
            UserActivityLog::Name_UserActivityLog_Clear,
            "Pengguna '$user->username' telah membersihkan riwayat aktifitas pengguna.",
        );
    }

    public function delete($id)
    {
        $item = $this->find($id);
        $item->delete();
        return $item;
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

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
                $query->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        $q->select(['id', 'logged_at', 'activity_name', 'activity_category', 'description', 'user_id', 'username']);

        return $q->paginate($options['per_page'])->withQueryString();
    }
}
