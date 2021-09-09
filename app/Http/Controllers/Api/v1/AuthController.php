<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Auth\LoginRequest;
use App\Http\Requests\Api\v1\Auth\RegisterRequest;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    protected $maxAttempts = 3;

    /**
     * Handle the incoming request.
     *
     * @param LoginRequest $request
     * @return mixed
     * @throws GuzzleException
     */
    public function login(LoginRequest $request)
    {
        try {
            // TODO: This should be removed in production since it is a security measure
            // Without this it crashes on local development
            $http = new Client(['verify' => false]);

            $client = DB::table('oauth_clients')->where('password_client', 1)->first();

            $response = $http->post(route('passport.token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $client->id,
                    'client_secret' => $client->secret,
                    'username' => $request->email,
                    'password' => $request->password,
                    'scope' => '',
                ],
            ]);

            return json_decode((string)$response->getBody(), true);
        } catch (ClientException $e) {
            $error = json_decode((string)$e->getResponse()->getBody());

            return response()->json([
                    'title' => 'Bad Request',
                    'detail' => $error->message,
                    'status' => '400',
                ], 400);
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();

        return response()->json([], 204);
    }

    /**
     * Handle the incoming request.
     *
     * @param RegisterRequest $request
     * @return mixed
     */
    public function register(RegisterRequest $request)
    {
        User::create($request->validated());

        return response()->json(['message' => 'Account successfully created!'], 201);
    }
}
