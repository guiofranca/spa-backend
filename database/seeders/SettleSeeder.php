<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Settle;
use App\Models\User;
use App\Services\SettlerService;
use Illuminate\Database\Seeder;

class SettleSeeder extends Seeder
{
    public const PENDING_BILLS = 5;

    /**
     * Run the database seeds.
     *
     * Creates 2 users, a single group with a 50/50 split, 24 months of
     * settles, each containing 20 randomly generated bills (values between
     * 10 and 200) with the matching settle fragments, plus pending bills
     * that have not been settled yet.
     */
    public function run(): void
    {
        $alice = User::firstOrCreate(['email' => 'alice@example.com'], [
            'name' => 'Alice',
            'password' => 'password',
        ]);

        $bob = User::firstOrCreate(['email' => 'bob@example.com'], [
            'name' => 'Bob',
            'password' => 'password',
        ]);

        $group = Group::firstOrCreate(['owner_id' => $alice->id, 'name' => 'Casa'], [
            'description' => 'Despesas compartilhadas da casa',
        ]);

        Bill::where('group_id', $group->id)->delete();
        Settle::where('group_id', $group->id)->delete();
        GroupMember::where('group_id', $group->id)->delete();

        $alice->update(['active_group_id' => $group->id]);
        $bob->update(['active_group_id' => $group->id]);

        GroupMember::create([
            'user_id' => $alice->id,
            'group_id' => $group->id,
            'contribution' => 50,
        ]);

        GroupMember::create([
            'user_id' => $bob->id,
            'group_id' => $group->id,
            'contribution' => 50,
        ]);

        $members = collect([$alice, $bob]);

        for ($i = 0; $i < 24; $i++) {
            $month = now()->startOfMonth()->subMonths(23 - $i);

            $settle = Settle::create([
                'name' => $month->translatedFormat('F Y'),
                'group_id' => $group->id,
                'date' => $month,
                'settled' => true,
            ]);

            for ($j = 0; $j < 20; $j++) {
                $category = Category::inRandomOrder()->first();

                Bill::create([
                    'description' => ucfirst($category->name) . ' ' . fake()->words(2, true),
                    'value' => fake()->randomFloat(2, 10, 200),
                    'user_id' => $members->random()->id,
                    'group_id' => $group->id,
                    'category_id' => $category->id,
                    'settle_id' => $settle->id,
                    'due_at' => $month->copy()->endOfMonth(),
                    'paid_at' => $month->copy()->addDays(random_int(1, 27)),
                ]);
            }

            SettlerService::makeSettle($settle->loadMissing(['bills', 'group.groupMembers']));
        }

        for ($i = 0; $i < self::PENDING_BILLS; $i++) {
            $category = Category::inRandomOrder()->first();

            Bill::create([
                'description' => ucfirst($category->name) . ' ' . fake()->words(2, true),
                'value' => fake()->randomFloat(2, 10, 200),
                'user_id' => $members->random()->id,
                'group_id' => $group->id,
                'category_id' => $category->id,
                'settle_id' => null,
                'due_at' => null,
                'paid_at' => now()->endOfMonth(),
            ]);
        }
    }
}