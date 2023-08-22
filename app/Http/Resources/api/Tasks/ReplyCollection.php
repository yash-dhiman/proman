<?php

namespace App\Http\Resources\Api\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Api\Tasks\ReplyResource;

class ReplyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return ReplyResource::collection($this->resource);
    }
}
