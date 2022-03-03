<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Settle;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SettleResourceCollection extends ResourceCollection
{
    public $collects = SettleResource::class;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
