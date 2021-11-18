<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public User $user1, $user2;

    public function setUp(): void
    {
        parent::setUp();
        $users = User::factory(2)->create();
        $this->user1 = $users[0];
        $this->user2 = $users[1];
    }

    public function test_user_can_create_groups()
    {
        $this->json('GET', '/api/v1/groups')
            ->assertStatus(401);

        $this->actingAs($this->user1)
            ->json('GET', '/api/v1/groups')
            ->assertJsonPath('data', [])
            ->assertJsonCount(0, 'data')
            ->assertStatus(200);
        
        $this->actingAs($this->user1)
            ->json('POST', '/api/v1/groups', Group::factory()->make()->only('name', 'description'))
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Group sucessfully created']);

        $this->actingAs($this->user1)
            ->json('GET', '/api/v1/groups')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
            
        $g = Group::factory()->create([
            'owner_id' => $this->user1->id
        ]);
        GroupMember::create([
            'user_id' => $this->user1->id,
            'group_id' => $g->id,
            'contribution' => 100,
        ]);

        $this->actingAs($this->user1)
            ->json('GET', '/api/v1/groups')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_view_group()
    {
        $group1 = $this->create_a_group($this->user1);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/groups/1')
            ->assertStatus(200);

        $this->actingAs($this->user2)
            ->json('get', '/api/v1/groups/1')
            ->assertStatus(403);

        $this->invite_to_group($group1, $this->user2);

        $this->actingAs($this->user2)
            ->json('get', '/api/v1/groups/1')
            ->assertStatus(200);
    }

    public function test_changing_group_attributes()
    {
        $group1 = $this->create_a_group($this->user1);
        $this->invite_to_group($group1, $this->user2);

        $this->actingAs($this->user1)
            ->json('patch', '/api/v1/groups/1', [
                'name' => 'Test group',
                'description' => 'testing',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test group']);
        
        $this->actingAs($this->user1)
            ->json('patch', '/api/v1/groups/1', [
                'name' => '',
                'description' => 'testing',
            ])
            ->assertStatus(422);
        
        $this->actingAs($this->user1)
            ->json('patch', '/api/v1/groups/1', [
                'name' => 'Test group',
                'description' => '',
            ])
            ->assertStatus(422);

        $this->actingAs($this->user2)
            ->json('patch', '/api/v1/groups/1', [
                'name' => 'Test group',
                'description' => 'testing',
            ])
            ->assertStatus(403);
    }

    public function test_only_the_owner_can_delete_a_group()
    {
        $group1 = $this->create_a_group($this->user1);
        $this->invite_to_group($group1, $this->user2);

        $this->actingAs($this->user2)
            ->json('delete', '/api/v1/groups/1')
            ->assertStatus(403);

        $this->actingAs($this->user1)
            ->json('delete', '/api/v1/groups/1')
            ->assertStatus(200);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/groups/1')
            ->assertStatus(404);
    }

    public function test_setting_an_active_group()
    {
        $group1 = $this->create_a_group($this->user1);

        $this->actingAs($this->user1)
            ->json('patch', '/api/v1/user/activegroup', [
                'active_group_id' => $group1->id
            ])
            ->assertStatus(200)
            ->assertJsonFragment(["message" => "Active group changed"]);
        
        $this->actingAs($this->user2)
            ->json('patch', '/api/v1/user/activegroup', [
                'active_group_id' => $group1->id
            ])
            ->assertStatus(403);

        $this->invite_to_group($group1, $this->user2);

        $this->actingAs($this->user2)
            ->json('patch', '/api/v1/user/activegroup', [
                'active_group_id' => $group1->id
            ])
            ->assertStatus(200)
            ->assertJsonFragment(["message" => "Active group changed"]);
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

    public function invite_to_group(Group $group, User $user): void
    {
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'contribution' => 100,
        ]);
    }
}
