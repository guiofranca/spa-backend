<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
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
            "description" => $this->description,
            "value" => $this->value,
            "user_id" => $this->user_id,
            "group_id" => $this->group_id,
            "category_id" => $this->category_id,
            "settle_id" => $this->settle_id,
            "paid_at" => $this->paid_at,
            "due_at" => $this->due_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "user" => [
                "id" => $this->user->id,
                "name" => $this->user->name,
            ],
            "category" => [
                "id" => $this->category->id,
                "name" => $this->category->name,
                "icon" => $this->category->icon,
            ]
        ];
    }
}
