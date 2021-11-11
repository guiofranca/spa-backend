<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = $this->only([
            'id',
            'name',
            'description',
            'owner_id',
        ]);

        $resource['group_members'] = $this->groupMembers->map(function($member){
            $resource = $member->only([
                'id',
                'contribution',
            ]);

            $resource['name'] = $member->user->name;
            return $resource;
        });

        return $resource;
    }
}
