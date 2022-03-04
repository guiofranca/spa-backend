<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_must_login_correctly()
    {
        $this->json('post', '/api/v1/auth/login', ['password' => '123'])
            ->assertStatus(422);

        $this->json('post', '/api/v1/auth/login', ['email' => 'test@example.com'])
            ->assertStatus(422);
    }

    public function test_user_can_register()
    {
        $registrationOne = [
            'name' => $this->faker->name,
            'email' => 'test_one@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $registrationTwo = [
            'name' => $this->faker->name,
            'email' => 'test_two@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->json('post', '/api/v1/auth/register', $registrationOne);

        $response->assertStatus(201);

        $response = $this->json('post', '/api/v1/auth/register', $registrationTwo);

        $response->assertStatus(201);

        $response = $this->json('post', '/api/v1/auth/register', $registrationTwo);

        $response->assertStatus(422);
    }

    public function test_show_user_details()
    {
        $this->actingAs($this->user)
            ->json('get', '/api/v1/user')
            ->assertStatus(200);
    }

    public function test_user_can_change_profile()
    {
        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => 'Changing',
                'email' => 'change@test.com',
            ])
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->json('get', '/api/v1/user')
            ->assertJsonFragment(['name' => 'Changing'])
            ->assertJsonFragment(['email' => 'change@test.com']);

        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => 'Changing',
                'email' => 'invalidemailtest.com',
            ])
            ->assertStatus(422);
        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => '',
                'email' => 'change@test.com',
            ])
            ->assertStatus(422);
    }

    public function test_user_change_password()
    {
        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'current_password' => 'password',
                'password' => 'new_password_not_match',
                'password_confirmation' => 'new_password',
            ])
            ->assertStatus(422);

        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'current_password' => 'password_not_match',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ])
            ->assertStatus(422);
        
        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'current_password' => '  ',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ])
            ->assertStatus(422);
        
        $this->actingAs($this->user)
            ->json('patch', '/api/v1/user', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'current_password' => 'password',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ])
            ->assertStatus(200);
    }

}
