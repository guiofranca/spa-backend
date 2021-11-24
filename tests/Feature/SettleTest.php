<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Settle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SettleTest extends TestCase
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

    public function test_cant_create_settle_without_bills()
    {
        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', ['name' => 'test settle'])
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'You need unsettled bills to settle']);
    }
    
    public function test_cant_create_settle_without_active_group()
    {
        $this->actingAs($this->user3)
            ->json('post', '/api/v1/settles', ['name' => 'test settle'])
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'You need to be in a group you own to make a settle']);
    }
    
    public function test_only_group_owner_can_create_settles()
    {
        $this->create_bills($this->user1, 1, 100);
        $this->create_bills($this->user2, 3, 100);
        $this->actingAs($this->user2)
            ->json('post', '/api/v1/settles', ['name' => 'test settle'])
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'You need to be in a group you own to make a settle']);
            
        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', ['name' => 'test settle'])
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Settle sucessfully created']);
    }

    public function test_only_group_owner_can_edit_settle()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->user1->active_group_id,
            'date' => now(),
            'settled' => false,
        ]);

        $this->actingAs($this->user1)
            ->json('patch', "/api/v1/settles/{$settle->id}", [
                'name' => 'settling',
                'settled' => true,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'settling'])
            ->assertJsonFragment(['settled' => true])
            ->assertJsonFragment(['message' => 'Settle sucessfully updated']);
        
        $this->actingAs($this->user2)
            ->json('patch', "/api/v1/settles/{$settle->id}", [
                'name' => 'settling',
                'settled' => true,
            ])
            ->assertStatus(403)
            ->assertJsonFragment(['message' => "You can't change a settle from a group you do not own"]);
    }

    public function test_unsettled_bills_are_associated_to_settle_when_created()
    {
        $this->create_bills($this->user1, 5);
        $this->create_bills($this->user2, 5);

        $this->assertCount(10, $this->user1->active_group->unsettledBills);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(10, 'data.bills');

        $this->assertCount(0, $this->user1->active_group->fresh()->unsettledBills);

        $this->actingAs($this->user1)
            ->json('get', "/api/v1/settles/1")
            ->assertStatus(200)
            ->assertJsonCount(10, 'data.bills');
    }

    public function test_only_owner_can_delete_settle()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->user1->active_group_id,
            'date' => now(),
            'settled' => false,
        ]);

        $this->actingAs($this->user2)
            ->json('delete', "/api/v1/settles/{$settle->id}")
            ->assertStatus(403);

        $this->actingAs($this->user1)
            ->json('delete', "/api/v1/settles/{$settle->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Settle successfully deleted']);

        $this->assertNull($settle->fresh());
    }

    public function test_bills_from_deleted_settle_goes_back_to_unsettled()
    {
        $this->create_bills($this->user1, 5);
        $this->create_bills($this->user2, 5);

        $this->assertCount(10, $this->user1->active_group->fresh()->unsettledBills);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(10, 'data.bills');
        
        $this->assertCount(0, $this->user1->active_group->fresh()->unsettledBills);
        
        $this->actingAs($this->user1)
            ->json('delete', "/api/v1/settles/1")
            ->assertStatus(200);
        
        $this->assertCount(10, $this->user1->active_group->fresh()->unsettledBills);
    }

    public function test_settle_fragments_are_correct()
    {
        $this->create_bills($this->user1, 1, 100);
        $this->create_bills($this->user2, 2, 100);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(3, 'data.bills')
            ->assertJsonCount(2, 'data.settleFragments')
            ->assertJsonPath('data.settleFragments.0.due', '50.00')
            ->assertJsonPath('data.settleFragments.1.to_receive', '50.00');

        $this->invite_to_group($this->group, $this->user3, 100);
        $this->user3->active_group_id = $this->group->id;

        $this->create_bills($this->user1, 1, 100);
        $this->create_bills($this->user2, 2, 100);
        $this->create_bills($this->user3, 3, 100);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(6, 'data.bills')
            ->assertJsonCount(3, 'data.settleFragments')
            ->assertJsonPath('data.settleFragments.0.due', '100.00')
            ->assertJsonPath('data.settleFragments.0.to_receive', '0.00')
            ->assertJsonPath('data.settleFragments.1.due', '0.00')
            ->assertJsonPath('data.settleFragments.1.to_receive', '0.00')
            ->assertJsonPath('data.settleFragments.2.due', '0.00')
            ->assertJsonPath('data.settleFragments.2.to_receive', '100.00');
    }

    public function test_settle_fragments_are_also_correct()
    {
        $this->create_bills($this->user1, 1, 75);
        $this->create_bills($this->user2, 2, 150);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(3, 'data.bills')
            ->assertJsonCount(2, 'data.settleFragments')
            ->assertJsonPath('data.settleFragments.0.due', '112.50')
            ->assertJsonPath('data.settleFragments.0.to_receive', '0.00')
            ->assertJsonPath('data.settleFragments.1.due', '0.00')
            ->assertJsonPath('data.settleFragments.1.to_receive', '112.50');

        $this->invite_to_group($this->group, $this->user3, 50);
        $this->user3->active_group_id = $this->group->id;

        $this->create_bills($this->user1, 1, 100);
        $this->create_bills($this->user2, 2, 100);
        $this->create_bills($this->user3, 3, 100);

        $this->actingAs($this->user1)
            ->json('post', '/api/v1/settles', Settle::factory()->make()->toArray())
            ->assertStatus(201)
            ->assertJsonCount(6, 'data.bills')
            ->assertJsonCount(3, 'data.settleFragments')
            ->assertJsonPath('data.settleFragments.0.due', '140.00')
            ->assertJsonPath('data.settleFragments.0.to_receive', '0.00')
            ->assertJsonPath('data.settleFragments.1.due', '40.00')
            ->assertJsonPath('data.settleFragments.1.to_receive', '0.00')
            ->assertJsonPath('data.settleFragments.2.due', '0.00')
            ->assertJsonPath('data.settleFragments.2.to_receive', '180.00');
    }

    protected function create_bills(User $user, int $amount, ?float $value = null): Collection
    {
        $create = [
            'user_id' => $user->id,
            'group_id' => $user->active_group_id,
        ];

        if(!is_null($value))
        {
            $create['value'] = $value;
        }

        $bills = Bill::factory($amount)->create($create);

        return $bills;
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

    public function invite_to_group(Group $group, User $user, int $contribution = 100): void
    {
        GroupMember::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'contribution' => $contribution,
        ]);
    }
}
