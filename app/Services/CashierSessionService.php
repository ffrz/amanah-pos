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

use App\Models\CashierSession;
use Illuminate\Support\Facades\Auth;

class CashierSessionService
{

    public static function getActiveSession()
    {
        return CashierSession::with(['cashierTerminal', 'cashierTerminal.financeAccount'])
            ->where('user_id', Auth::user()->id)
            ->where('is_closed', false)
            ->first();
    }
}
