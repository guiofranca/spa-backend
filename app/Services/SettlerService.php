<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Group;
use App\Models\Settle;
use App\Models\SettleFragment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        SettlerService::copyRecurringBills($settle);
        return $settleFragments;
    }

    private static function copyRecurringBills(Settle $settle) : void 
    {
        $pattern = "/(\d+)\/(\d+)/";
        $settle->bills->each(function(Bill $bill) use ($pattern) {
            $description = preg_replace_callback($pattern, function($matches) {
                $numerator = intval($matches[1]) + 1; 
                $denominator = intval($matches[2]);
                if($numerator > $denominator) return $matches[0];
                $result = $numerator . "/" . $denominator;
                return $result;
            }, $bill->description);

            if($description != $bill->description) {
                $newBill = $bill->replicate();
                $newBill->description = $description;
                $newBill->settle_id = null;
                $newBill->paid_at = $newBill->paid_at->addMonth(1);
                $newBill->save();
                return;
            }

            if(Str::contains($bill->description, "mensal", true)) {
                $newBill = $bill->replicate();
                $newBill->settle_id = null;
                $newBill->paid_at = $newBill->paid_at->addMonth(1);
                $newBill->save();
                return;
            }
        });
    }
}