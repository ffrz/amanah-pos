<?php

namespace Modules\Admin\Policies;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserPolicy extends DefaultPolicy
{
    public function update(User $user, Model $model): bool
    {
        return $user->id !== $model->id;
    }

    public function delete(User $user, Model $model): bool
    {
        return $model->username !== 'admin' && $user->id !== $model->id;
    }
}
