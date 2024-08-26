<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\User\SetActiveGroupRequest;
use App\Http\Requests\Api\v1\User\UpdateProfileRequest;
use App\Http\Resources\Api\v1\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

/**
 * @tags v1 Usuários
 */
class UserController extends Controller
{
    /**
     * Atualizar informações de perfil
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        Auth::user()->update($request->validated());

        return response()->json([
            'message' => __('Profile information changed successfully!'),
        ]);
    }

    /**
     * Informações do Usuário
     */
    public function user(){
        return new UserResource(Auth::user()->load('active_group'));
    }

    /**
     * Escolher o grupo ativo
     */
    public function setActiveGroup(SetActiveGroupRequest $request){
        Auth::user()->update($request->validated());

        return response()->json([
            'message' => __('Active group changed'),
        ]);
    }
}
