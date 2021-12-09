<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Settle;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SettleResourceCollection extends ResourceCollection
{
    public $collects = Settle::class;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->map(function($settle) {
            return new SettleResource($settle);
        });
    }
}
