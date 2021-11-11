<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Group;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupResourceCollection extends ResourceCollection
{
    public $collects = Group::class;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->map(function($group) {
            return new GroupResource($group);
        });
    }
}
