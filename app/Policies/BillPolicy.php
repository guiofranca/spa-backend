<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BillPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return isset($user->active_group_id)
            ? Response::allow()
            : Response::deny("Please choose a group first");
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Bill $bill)
    {
        return $user->active_group_id == $bill->group_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return isset($user->active_group_id)
            ? Response::allow()
            : Response::deny("Please choose a group first");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Bill $bill)
    {
        return $bill->isOwnedBy($user)
            ? Response::allow()
            : Response::deny("You should only edit bills that you created");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Bill $bill)
    {
        return $bill->isOwnedBy($user)
        ? Response::allow()
        : Response::deny("You should only delete bills that you created");
    }
}
