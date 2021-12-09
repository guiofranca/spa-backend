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
        $resource = $this->only([
            'id',
            'description',
            'value',
            'user_id',
            'group_id',
            'category_id',
            'settle_id',
            'paid_at',
            'due_at',
            'created_at',
            'updated_at',
        ]);

        $resource['value'] = $resource['value'];

        $resource['user'] = $this->user->only('id', 'name');
        $resource['category'] = new CategoryResource($this->category);
        return $resource;
    }
}
