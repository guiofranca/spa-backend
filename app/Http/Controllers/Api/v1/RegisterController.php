<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Auth\RegisterRequest;
use App\Models\User;

/**
 * @tags v1 Cadastro de usuário
 */
class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        User::create($request->validated());

        return response()->json([
            'message' => __('Account successfully created!'),
        ], 201);
    }
}
