<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "active_group_id" => $this->active_group_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "active_group" => [
                "id" => $this->active_group?->id,
                "name" => $this->active_group?->name,
                "description" => $this->active_group?->description,
                "owner_id" => $this->active_group?->owner_id,
                "created_at" => $this->active_group?->created_at,
                "updated_at" => $this->active_group?->updated_at,
            ]
        ];
    }
}
