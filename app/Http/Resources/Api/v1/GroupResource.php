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
            return new GroupMemberResource($member);
        });

        return $resource;
    }
}
