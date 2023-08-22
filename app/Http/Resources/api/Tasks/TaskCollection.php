<?php

namespace App\Http\Resources\api\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\api\Tasks\TaskResource;

class TaskCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success'   => true,
            'message'   => 'Tasks data',
            'data'      => TaskResource::collection($this->resource),
        ];
    }
}
