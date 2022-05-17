<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->user = $user;
    }

    public function test_incorrect_credentials_fails_login()
    {
        $this->json('POST', '/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrong_password'
        ])->assertStatus(401);
    }

    public function test_login_should_throttle_after_3_times()
    {
        for($i = 1; $i <= 3; $i++) {
            $response = $this->json('POST', '/api/v1/auth/login', [
                'email' => $this->user->email,
                'password' => 'wrong_password'
            ])
            ->assertHeader("X-RateLimit-Limit", "3")
            ->assertHeader("X-RateLimit-Remaining", (string) (3 - $i));
        }

        $this->json('POST', '/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrong_password'
        ])->assertStatus(429);

        
    }

    public function test_login_returns_token()
    {
        $response = $this->json('POST', '/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ])->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'expires_in',
            'token_type',
        ]);

        $token = json_decode($response->getContent())->access_token;

        $this->assertEquals(3, count(explode(".", $token)));
    }

    public function test_token_must_be_used_for_authorized_access()
    {
        $this->json('get', '/api/v1/user', [], [])
            ->assertStatus(401);

        $token = $this->generateToken();

        $this->json('get', '/api/v1/user', [], [
            'Authorization' => "bearer {$token}wrong",
        ])->assertStatus(401);

        auth()->unsetToken();

        $this->json('get', '/api/v1/user', [], [
            'Authorization' => "bearer {$token}",
        ])->assertStatus(200);
    }

    public function test_logout_invalidates_token()
    {
        $token = $this->generateToken();

        $this->json('post', '/api/v1/auth/logout', [], [
            'Authorization' => "bearer {$token}",
        ])->assertStatus(200);

        $this->json('get', '/api/v1/user', [], [
            'Authorization' => "bearer {$token}",
        ])->assertStatus(401);
            
        $this->expectException('Tymon\JWTAuth\Exceptions\TokenBlacklistedException');
        auth()->getPayload();
    }

    public function test_user_refreshes_token_and_invalidates_the_old_token()
    {
        $token = $this->generateToken();

        $response = $this->json('post', '/api/v1/auth/refresh', [], [
            'Authorization' => "bearer {$token}",
        ])->assertStatus(200);

        $this->assertNotEquals($token, json_decode($response->getContent())->access_token);

        $this->expectException('Tymon\JWTAuth\Exceptions\TokenBlacklistedException');
        auth()->getPayload();
    }

    public function generateToken() : string
    {
        $token = auth()->tokenById(1);

        return $token;
    }
}
