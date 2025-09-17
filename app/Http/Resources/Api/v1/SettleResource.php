<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class SettleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->loadMissing('group.groupMembers.user');
        $this->loadMissing('bills.user');
        $this->loadMissing('bills.category');
        $this->loadMissing('settleFragments.user');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'settled' => $this->settled,
            'date' => $this->date,
            'group' => new GroupResource($this->group),
            'bills' => BillResource::collection($this->bills),
            'settleFragments' => SettleFragmentResource::collection($this->settleFragments),
            'total' => sprintf('%.2f', $this->bills->sum('value')),
        ];
    }
}
