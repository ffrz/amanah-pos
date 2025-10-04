<?php

namespace Modules\Admin\Policies;

use App\Models\BaseModel;
use App\Models\User;

class UserPolicy extends DefaultPolicy
{
    public function update(User $user, BaseModel $model): bool
    {
        return $user->id !== $model->id;
    }

    public function delete(User $user, BaseModel $model): bool
    {
        return $model->username !== 'admin' && $user->id !== $model->id;
    }
}
