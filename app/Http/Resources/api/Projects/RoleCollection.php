<?php

namespace App\Http\Resources\Api\Projects;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Api\Projects\RoleResource;

class RoleCollection extends ResourceCollection
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
                    'message'   => 'Comments data',
                    'data'      => RoleResource::collection($this->resource),
                ];
    }
}
