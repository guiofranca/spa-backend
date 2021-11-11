<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed();
    }

    public function test_user_can_view_categories()
    {
        $this->json('GET', '/api/v1/categories')
            ->assertStatus(401);

        $this->actingAs($this->user)
            ->json('GET', '/api/v1/categories')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'General']);
    }
}
