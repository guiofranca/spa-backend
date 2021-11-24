<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class SettleFragmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $resource = [];
        $resource['name'] = $this->user->name;
        $resource['paid'] = $this->paid;
        $resource['contribute'] = $this->contribute;
        $resource['due'] = $this->due;
        $resource['to_receive'] = $this->to_receive;

        return $resource;
    }
}
