<?php

namespace App\Policies;

use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GroupMemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GroupMember  $groupMember
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, GroupMember $groupMember)
    {
        return $groupMember->group->isOwnedBy($user)
            ? Response::allow()
            : Response::deny("You can't change the members of a group you do not own");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\GroupMember  $groupMember
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, GroupMember $groupMember)
    {
        return $groupMember->group->isOwnedBy($user) && $groupMember->user->id != $user->id
            ? Response::allow()
            : Response::deny("You can't remove yourself");
    }
}
