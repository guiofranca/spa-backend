<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Group;
use App\Models\Settle;
use App\Models\SettleFragment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettleSeeder;

class SettleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_creates_two_users_one_group_and_a_50_50_split(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        $this->assertCount(2, User::all());
        $this->assertCount(1, Group::all());

        $group = Group::with('groupMembers')->first();

        $this->assertCount(2, $group->groupMembers);
        $this->assertTrue($group->groupMembers->pluck('contribution')->every(fn ($contribution) => $contribution === 50));

        $group->groupMembers->each(function ($member) use ($group): void {
            $this->assertSame($group->id, $member->group_id);
            $this->assertSame($group->id, User::find($member->user_id)->active_group_id);
        });
    }

    public function test_seed_creates_24_settles_with_20_bills_each(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        $this->assertCount(24, Settle::all());
        $this->assertSame((24 * 20) + SettleSeeder::PENDING_BILLS, Bill::count());

        Settle::with('bills')->get()->each(function (Settle $settle): void {
            $this->assertCount(20, $settle->bills);
            $this->assertTrue($settle->bills->pluck('settle_id')->every(fn ($settleId) => $settleId === $settle->id));
        });
    }

    public function test_seed_creates_pending_bills_without_a_settle(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        $pendingBills = Bill::whereNull('settle_id')->get();

        $this->assertCount(SettleSeeder::PENDING_BILLS, $pendingBills);

        $pendingBills->each(function (Bill $bill): void {
            $this->assertNotNull($bill->category_id);
            $this->assertNotNull($bill->paid_at);
            $this->assertNull($bill->due_at);
            $this->assertNull($bill->settle_id);
            $this->assertGreaterThanOrEqual(10, (float) $bill->value);
            $this->assertLessThanOrEqual(200, (float) $bill->value);
        });
    }

    public function test_seed_creates_one_month_between_each_settle(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        $dates = Settle::orderBy('date')->pluck('date');

        for ($i = 1; $i < $dates->count(); $i++) {
            $previous = $dates[$i - 1]->copy()->addMonth();
            $current = $dates[$i];

            $this->assertSame($previous->format('Y-m'), $current->format('Y-m'), "Expected one month between {$dates[$i - 1]->format('Y-m')} and {$current->format('Y-m')}");
        }
    }

    public function test_seed_bill_values_range_between_10_and_200(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        Bill::all()->each(function (Bill $bill): void {
            $this->assertGreaterThanOrEqual(10, (float) $bill->value);
            $this->assertLessThanOrEqual(200, (float) $bill->value);
        });
    }

    public function test_seed_settle_fragments_match_bill_totals(): void
    {
        $this->seed([
            CategorySeeder::class,
            SettleSeeder::class,
        ]);

        $this->assertSame(48, SettleFragment::count());

        Settle::with(['bills', 'settleFragments'])->get()->each(function (Settle $settle): void {
            $billTotal = $settle->bills->sum(fn (Bill $bill) => (float) $bill->value);

            $this->assertCount(2, $settle->settleFragments);

            $paid = $settle->settleFragments->sum(fn ($fragment) => (float) $fragment->paid);
            $contribute = $settle->settleFragments->sum(fn ($fragment) => (float) $fragment->contribute);

            $this->assertEqualsWithDelta($billTotal, $paid, 0.05);
            $this->assertEqualsWithDelta($billTotal, $contribute, 0.05);
        });
    }
}