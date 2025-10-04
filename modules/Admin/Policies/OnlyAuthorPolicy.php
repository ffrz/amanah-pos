<?php

namespace Modules\Admin\Policies;

use App\Models\BaseModel;
use App\Models\User;

class OnlyAuthorPolicy extends DefaultPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BaseModel $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BaseModel $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BaseModel $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }
}
