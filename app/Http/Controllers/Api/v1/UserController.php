<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\User\SetActiveGroupRequest;
use App\Http\Requests\Api\v1\User\UpdateProfileRequest;
use App\Http\Resources\Api\v1\UserResource;

class UserController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request)
    {
        if(
            $request->has(['current_password', 'password', 'password_confirmation'])
            && !\Hash::check($request->current_password, auth()->user()->password)
        ){
            return response()->json(['message' => 'Current password does not match.'], 422);
        }

        auth()->user()->update($request->validated());

        return response()->json(['message' => 'Profile information changed successfully!']);
    }

    public function user(){
        return new UserResource(auth()->user()->load('active_group'));
    }

    public function setActiveGroup(SetActiveGroupRequest $request){
        auth()->user()->update($request->validated());

        return response()->json(['message' => 'Active group changed']);
    }
}
