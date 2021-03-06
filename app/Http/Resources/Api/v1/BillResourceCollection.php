<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Bill;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BillResourceCollection extends ResourceCollection
{
    public $collects = BillResource::class;
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
