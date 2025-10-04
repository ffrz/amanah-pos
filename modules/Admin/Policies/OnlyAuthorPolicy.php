<?php

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
