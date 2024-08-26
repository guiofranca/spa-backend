<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Requests\Api\v1\User\SetActiveGroupRequest;
use App\Http\Requests\Api\v1\User\UpdateProfileRequest;
use App\Http\Resources\Api\v1\UserResource;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class UserController extends Controller
{

    public function __construct(private Auth $auth)
    {
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $this->auth->user()->update($request->validated());

        return response()->json([
            'message' => __('Profile information changed successfully!'),
        ]);
    }


    #[OA\Get(
        path: 'lelele',
        method: 'POST',
        summary: 'yeye'
    )]
    public function user()
    {
        return new UserResource($this->auth->user()->load('active_group'));
    }

    public function setActiveGroup(SetActiveGroupRequest $request)
    {
        $this->auth->user()->update($request->validated());

        return response()->json([
            'message' => __('Active group changed'),
        ]);
    }
}
