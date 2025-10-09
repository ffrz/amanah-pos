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

namespace Modules\Admin\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OnlyAuthorPolicy extends DefaultPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }
}
