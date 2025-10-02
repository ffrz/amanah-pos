<?php

namespace Modules\Admin\Policies;

use App\Models\OperationalCost;
use App\Models\User;

class OperationalCostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OperationalCost $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperationalCost $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperationalCost $item): bool
    {
        return $user->type == User::Type_SuperUser || $user->id == $item->created_by;
    }
}
