<?php

namespace App\Policies;

use App\Models\Settle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SettlePolicy
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
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Settle $settle)
    {
        return $settle->group->hasMember($user) && $settle->group_id == $user->active_group_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $user->load('active_group.unsettledBills');

        if(!$user->active_group?->isOwnedBy($user))
        {
            return Response::deny("You need to be in a group you own to make a settle");
        }

        return $user?->active_group?->unsettledBills->isnotEmpty()
            ? Response::allow()
            : Response::deny("You need unsettled bills to settle");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Settle $settle)
    {
        return $settle->group->isOwnedBy($user)
            ? Response::allow()
            : Response::deny("You can't change a settle from a group you do not own");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Settle $settle)
    {
        return $settle->group->isOwnedBy($user);
    }
}
