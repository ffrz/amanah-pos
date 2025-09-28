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

namespace App\Services;

use App\Models\UserActivityLog;
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
     * @param array $metadata Data tambahan yang kompleks.
     * @return UserActivityLog
     */
    public function log(
        string $category,
        string $name,
        string $description,
        array $metadata = []
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
}
