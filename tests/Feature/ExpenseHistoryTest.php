<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Settle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseHistoryTest extends TestCase
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

    public function test_requires_an_active_group()
    {
        $this->actingAs($this->user3)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(403);
    }

    public function test_history_is_grouped_by_settle_for_active_group()
    {
        $settle1 = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-31',
            'settled' => true,
        ]);
        $settle2 = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-02-28',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'user_id' => $this->user1->id,
            'category_id' => 1,
            'settle_id' => $settle1->id,
            'value' => 10.50,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'user_id' => $this->user2->id,
            'category_id' => 2,
            'settle_id' => $settle1->id,
            'value' => 100.25,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'user_id' => $this->user1->id,
            'category_id' => 3,
            'settle_id' => $settle2->id,
            'value' => 7.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', $settle1->id)
            ->assertJsonPath('data.bars.0.total', '110.75')
            ->assertJsonPath('data.bars.1.settle_id', $settle2->id)
            ->assertJsonPath('data.bars.1.total', '7.00');
    }

    public function test_pending_bills_appear_as_bar_for_today()
    {
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'user_id' => $this->user1->id,
            'category_id' => 1,
            'settle_id' => null,
            'value' => 25.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', null)
            ->assertJsonPath('data.bars.0.name', 'Em aberto')
            ->assertJsonPath('data.bars.0.settled', false)
            ->assertJsonPath('data.bars.0.date', now()->format('Y-m-d'))
            ->assertJsonPath('data.bars.0.total', '25.00');
    }

    public function test_pending_bills_follow_the_period_filter_by_today()
    {
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'user_id' => $this->user1->id,
            'category_id' => 1,
            'settle_id' => null,
            'value' => 7.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?from=2020-01-01&to=2099-12-31')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', null);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?from=2010-01-01&to=2011-12-31')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data.bars');
    }

    public function test_period_filter_uses_settle_date()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-03-10',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 50.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?from=2026-01-01&to=2026-12-31')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', $settle->id);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?from=2025-01-01&to=2025-12-31')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data.bars');
    }

    public function test_every_category_is_its_own_series_without_filter()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 2,
            'settle_id' => $settle->id,
            'value' => 20.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 2,
            'settle_id' => $settle->id,
            'value' => 5.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.series')
            ->assertJsonPath('data.series.0.name', Category::find(1)->name)
            ->assertJsonPath('data.series.0.values.0', '10.00')
            ->assertJsonPath('data.series.1.name', Category::find(2)->name)
            ->assertJsonPath('data.series.1.values.0', '25.00');
    }

    public function test_category_filter_aggregates_nonselected_into_others()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 2,
            'settle_id' => $settle->id,
            'value' => 20.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 3,
            'settle_id' => $settle->id,
            'value' => 30.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?categories[]=1')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.series')
            ->assertJsonPath('data.series.0.category_id', 1)
            ->assertJsonPath('data.series.0.values.0', '10.00')
            ->assertJsonPath('data.series.1.category_id', null)
            ->assertJsonPath('data.series.1.name', 'Outros')
            ->assertJsonPath('data.series.1.is_aggregate', true)
            ->assertJsonPath('data.series.1.values.0', '50.00');
    }

    public function test_selected_categories_appear_alongside_others()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 2,
            'settle_id' => $settle->id,
            'value' => 20.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 3,
            'settle_id' => $settle->id,
            'value' => 30.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?categories[]=2&categories[]=3')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.series')
            ->assertJsonPath('data.series.0.category_id', 2)
            ->assertJsonPath('data.series.0.values.0', '20.00')
            ->assertJsonPath('data.series.1.category_id', 3)
            ->assertJsonPath('data.series.1.values.0', '30.00')
            ->assertJsonPath('data.series.2.name', 'Outros')
            ->assertJsonPath('data.series.2.values.0', '10.00');
    }

    public function test_null_category_folds_into_others_when_filtering()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => null,
            'settle_id' => $settle->id,
            'value' => 8.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?categories[]=1')
            ->assertStatus(200)
            ->assertJsonPath('data.series.1.name', 'Outros')
            ->assertJsonPath('data.series.1.values.0', '8.00');
    }

    public function test_null_category_is_its_own_series_without_filter()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);
        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => null,
            'settle_id' => $settle->id,
            'value' => 8.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.series')
            ->assertJsonPath('data.series.1.name', 'Sem categoria')
            ->assertJsonPath('data.series.1.values.0', '8.00');
    }

    public function test_series_values_are_aligned_to_bars()
    {
        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-15',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 10.00,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 2,
            'settle_id' => null,
            'value' => 5.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.bars')
            ->assertJsonCount(2, 'data.series')
            ->assertJsonCount(2, 'data.series.0.values')
            ->assertJsonCount(2, 'data.series.1.values')
            ->assertJsonPath('data.series.0.values.0', '10.00')
            ->assertJsonPath('data.series.0.values.1', null)
            ->assertJsonPath('data.series.1.values.0', null)
            ->assertJsonPath('data.series.1.values.1', '5.00');
    }

    public function test_history_never_leaks_bills_or_settles_from_other_groups()
    {
        $otherGroup = $this->create_a_group($this->user3);
        $this->user3->active_group_id = $otherGroup->id;
        $this->user3->save();

        $settle = Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-10',
            'settled' => true,
        ]);
        $otherSettle = Settle::factory()->create([
            'group_id' => $otherGroup->id,
            'date' => '2026-01-10',
            'settled' => true,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $settle->id,
            'value' => 5.00,
        ]);

        Bill::factory()->create([
            'group_id' => $otherGroup->id,
            'category_id' => 1,
            'settle_id' => $otherSettle->id,
            'value' => 999.00,
        ]);

        Bill::factory()->create([
            'group_id' => $this->group->id,
            'category_id' => 1,
            'settle_id' => $otherSettle->id,
            'value' => 999.00,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', $settle->id)
            ->assertJsonPath('data.bars.0.total', '5.00');

        $this->actingAs($this->user3)
            ->json('get', '/api/v1/expenses/history')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.bars')
            ->assertJsonPath('data.bars.0.settle_id', $otherSettle->id)
            ->assertJsonPath('data.bars.0.total', '999.00');
    }

    public function test_validation_rejects_invalid_filters()
    {
        Settle::factory()->create([
            'group_id' => $this->group->id,
            'date' => '2026-01-10',
            'settled' => true,
        ]);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?categories[]=999')
            ->assertStatus(422);

        $this->actingAs($this->user1)
            ->json('get', '/api/v1/expenses/history?from=2026-02-01&to=2026-01-01')
            ->assertStatus(422);
    }

    public function create_a_group(User $user): Group
    {
        $g = Group::factory()->create([
            'owner_id' => $user->id,
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