<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Settle;
use App\Models\SettleFragment;
use Illuminate\Support\Collection;

class SettlerService
{
    public static function makeSettle(Settle $settle): Collection
    {
        $billTotal = $settle->bills->sum('value');
        
        $totalContribution = $settle->group->groupMembers->sum('contribution');

        if($totalContribution == 0) throw new \Exception("Total contribution can't be equal to zero");

        $settleFragments = new Collection();

        $settle->group->groupMembers->each(function($groupMember) use (&$settleFragments, $settle, $totalContribution, $billTotal){
            $settleFragment = new SettleFragment([
                'settle_id' => $settle->id,
                'user_id' => $groupMember->user_id,
                'paid' => $settle->bills->where('user_id', $groupMember->user_id)->sum('value'),
                'contribute' => $billTotal * $groupMember->contribution / $totalContribution,
            ]);

            if($settleFragment->paid >= $settleFragment->contribute)
            {
                $settleFragment->to_receive = $settleFragment->paid - $settleFragment->contribute;
                $settleFragment->due = 0;
            } else
            {
                $settleFragment->due = $settleFragment->contribute - $settleFragment->paid;
                $settleFragment->to_receive = 0;
            }

            $settleFragments->push($settleFragment);
        });

        $settleFragments->each(fn($settleFragment) => $settleFragment->save());

        return $settleFragments;
    }
}