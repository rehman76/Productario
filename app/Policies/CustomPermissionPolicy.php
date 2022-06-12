<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomPermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function before($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAnyPermission(['manage ' . static::$key]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function delete(User $user, $model)
    {
        if ($user->hasPermissionTo('delete ' . static::$key) ) {
            return true;
        }

        if ($user->hasPermissionTo('delete ' . static::$key)) {
            return $user->id == $model->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function forceDelete(User $user, $model)
    {
        if ($user->hasPermissionTo('forceDelete ' . static::$key)) {
            return true;
        }


        return false;
    }

//    /**
//     * Determine whether the user can restore the model.
//     *
//     * @param  \App\Models\User $user
//     * @return mixed
//     */
//    public function restore(User $user, $model)
//    {
//        if ($user->hasPermissionTo('restore ' . static::$key)) {
//            return true;
//        }
//
//
//        return false;
//    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function update(User $user, $model)
    {
        if ($user->hasPermissionTo('manage ' . static::$key)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function view(User $user, $model)
    {
        if ($user->hasPermissionTo('view ' . static::$key)) {
            return true;
        }

        if ($user->hasPermissionTo('view own ' . static::$key)) {
            return $user->id == $model->user_id;
        }

        return false;
    }

    /**
     * @param User $user
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view ' . static::$key);
    }
}
