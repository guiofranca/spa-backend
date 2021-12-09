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
        $resource = $this->only([
            'id',
            'name',
            'settled',
            'date',
        ]);
        $resource['group'] = new GroupResource($this->group);
        $resource['bills'] = new BillResourceCollection($this->bills);
        $resource['settleFragments'] = new SettleFragmentResourceCollection($this->settleFragments);
        $resource['total'] = sprintf('%.2f', $this->bills->sum('value'));
        return $resource;
        return parent::toArray($request);
    }
}
