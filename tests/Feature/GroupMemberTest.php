<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GroupMemberTest extends TestCase
{

    use WithFaker;
    use RefreshDatabase;

    public User $user1, $user2, $user3;
    public Group $group;

    public function setUp(): void
    {
        parent::setUp();
        $users = User::factory(3)->create();
        $this->user1 = $users[0];
        $this->user2 = $users[1];
        $this->user3 = $users[2];

        $this->group = $this->create_a_group($this->user1);
        $this->invite_to_group($this->group, $this->user2);

        $this->user1->active_group_id = $this->group->id;
        $this->user2->active_group_id = $this->group->id;

        $this->user1->save();
        $this->user2->save();

        $this->seed();
    }

    public function test_only_group_owner_can_change_member()
    {
        $member = $this->user1->active_group->groupMembers()->where('user_id', $this->user2->id)->first();

        $this->actingAs($this->user2)
            ->json('patch', "/api/v1/group_members/{$member->id}", [
                'contribution' => 70
            ])
            ->assertStatus(403);

        $this->actingAs($this->user3)
            ->json('patch', "/api/v1/group_members/{$member->id}", [
                'contribution' => 70
            ])
            ->assertStatus(403);

        $this->actingAs($this->user1)
            ->json('patch', "/api/v1/group_members/{$member->id}", [
                'contribution' => 70
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['contribution' => 70]);
        
        $this->assertEquals(70, $member->fresh()->contribution);
    }

    public function test_only_owner_can_remove_member()
    {
        $member = $this->user1->active_group->groupMembers()->where('user_id', $this->user2->id)->first();

        $this->actingAs($this->user2)
            ->json('delete', "/api/v1/group_members/{$member->id}")
            ->assertStatus(403);

        $this->actingAs($this->user3)
            ->json('delete', "/api/v1/group_members/{$member->id}")
            ->assertStatus(403);

        $this->actingAs($this->user1)
            ->json('delete', "/api/v1/group_members/{$member->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['message' => __('Group Member removed')]);
        
        $this->assertNull($member->fresh());
    }

    public function test_owner_cant_self_remove()
    {
        $member = $this->user1->active_group->groupMembers()->where('user_id', $this->user1->id)->first();

        $this->actingAs($this->user1)
            ->json('delete', "/api/v1/group_members/{$member->id}")
            ->assertStatus(403)
            ->assertJsonFragment(['message' => __("You can't remove yourself")]);
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
