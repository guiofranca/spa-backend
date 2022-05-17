<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;

class GroupInvitationTest extends TestCase
{
    use RefreshDatabase;

    public User $user1, $user2;
    public Group $group;

    public function setUp(): void
    {
        parent::setUp();
        $users = User::factory(2)->create();
        $this->user1 = $users[0];
        $this->user2 = $users[1];

        $this->group = $this->create_a_group($this->user1);
    }

    public function test_user_can_create_invitation()
    {
        $this->actingAs($this->user1)
            ->json('POST', '/api/v1/group_invitation', ['group_id' => $this->group->id])
            ->assertStatus(201)
            ->assertJsonFragment(['message' => __('Invitation Created')]);

        $this->actingAs($this->user2)
            ->json('POST', '/api/v1/group_invitation', ['group_id' => $this->group->id])
            ->assertStatus(403);
    }

    public function test_user_can_view_invitation()
    {
        $token = $this->actingAs($this->user1)
            ->json('POST', '/api/v1/group_invitation', ['group_id' => $this->group->id])
            ->decodeResponseJson()
            ->json('token');
        
        $this->actingAs($this->user1)
            ->json('GET', "/api/v1/group_invitation/{$token}")
            ->assertStatus(200);
        
        $this->actingAs($this->user2)
            ->json('GET', "/api/v1/group_invitation/{$token}")
            ->assertStatus(200);
    }

    public function test_user_can_accept_invitation()
    {
        $token = $this->actingAs($this->user1)
            ->json('POST', '/api/v1/group_invitation', ['group_id' => $this->group->id])
            ->decodeResponseJson()
            ->json('token');

        $this->actingAs($this->user1)
            ->json('PATCH', "/api/v1/group_invitation/{$token}", ['accepted' => 1])
            ->assertStatus(403);

        $this->actingAs($this->user2)
            ->json('PATCH', "/api/v1/group_invitation/{$token}")
            ->assertStatus(422);
        
        $this->actingAs($this->user2)
            ->json('PATCH', "/api/v1/group_invitation/{$token}", ['accepted' => 1])
            ->assertStatus(200);
        
        $this->actingAs($this->user2)
            ->json('GET', "/api/v1/groups/{$this->group->id}")
            ->assertStatus(200);
    }

    public function test_user_can_reject_invitation()
    {
        $token = $this->actingAs($this->user1)
            ->json('POST', '/api/v1/group_invitation', ['group_id' => $this->group->id])
            ->decodeResponseJson()
            ->json('token');
        
        $this->actingAs($this->user2)
            ->json('PATCH', "/api/v1/group_invitation/{$token}", ['accepted' => 0])
            ->assertStatus(200);
        
        $this->actingAs($this->user2)
            ->json('GET', "/api/v1/groups/{$this->group->id}")
            ->assertStatus(403);
    }

    public function create_a_group(User $user): Group
    {
        $g = Group::factory()->create([
            'owner_id' => $user->id
        ]);
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $g->id,
            'contribution' => 100,
        ]);

        return $g;
    }
}
