<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\CategorySeeder;

class BillTest extends TestCase
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

    public function test_users_can_view_bills_from_active_group()
    {
        $bills = Bill::factory(8)->create([
            'user_id' => $this->user1->id,
            'group_id' => $this->user1->active_group_id,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/bills')
            ->assertJsonCount($bills->count(), 'data')
            ->assertStatus(200);
    }

    public function test_user_can_create_bill()
    {
        $this->actingAs($this->user1)
            ->json('post', '/api/v1/bills', Bill::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonFragment(['group_id' => $this->user1->active_group_id])
            ->assertJsonFragment(['message' => 'Bill sucessfully created']);

        $this->actingAs($this->user2)
            ->json('post', '/api/v1/bills', Bill::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonFragment(['group_id' => $this->user2->active_group_id])
            ->assertJsonFragment(['message' => 'Bill sucessfully created']);
    }

    public function test_user_outside_group_cant_create_bill()
    {
        $this->actingAs($this->user3)
            ->json('post', '/api/v1/bills', Bill::factory()->make()->toArray())
            ->assertStatus(422);
    }

    public function test_only_owner_can_edit_bill()
    {
        $bill = Bill::factory()->create([
            'user_id' => $this->user1->id,
            'group_id' => $this->user1->active_group_id,
        ]);

        $this->actingAs($this->user1)
            ->json('patch', "/api/v1/bills/{$bill->id}", [
                'value' => "100.15",
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['value' => '100.15']);

        $this->actingAs($this->user2)
            ->json('patch', "/api/v1/bills/{$bill->id}", [
                'value' => "100.16",
            ])
            ->assertStatus(403);
    }

    public function test_user_cant_view_bill_outside_group()
    {
        $bill = Bill::factory()->create([
            'user_id' => $this->user1->id,
            'group_id' => $this->user1->active_group_id,
        ]);

        $group2 = $this->create_a_group($this->user2);
        $this->user2->update(['active_group_id' => $group2->id]);

        $this->actingAs($this->user2)
            ->json('get', "/api/v1/bills/{$bill->id}")
            ->assertStatus(403);
    }

    public function test_only_owner_can_delete_bill()
    {
        $bill = Bill::factory()->create([
            'user_id' => $this->user1->id,
            'group_id' => $this->user1->active_group_id,
        ]);

        $this->actingAs($this->user2)
            ->json('delete', "/api/v1/bills/{$bill->id}")
            ->assertStatus(403);

        $this->actingAs($this->user3)
            ->json('delete', "/api/v1/bills/{$bill->id}")
            ->assertStatus(403);

        $this->actingAs($this->user1)
            ->json('delete', "/api/v1/bills/{$bill->id}")
            ->assertStatus(200);
        
        $this->assertNull($bill->fresh());
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
