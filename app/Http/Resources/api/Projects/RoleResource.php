<?php

namespace App\Http\Resources\api\Projects;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                    'role_id'                   => $this->resource['role_id'] ? obfuscate($this->resource['role_id']) : '',
                    'title'                     => $this->resource['title'] ? $this->resource['title'] : '',
                    'description'               => $this->resource['description'] ? $this->resource['description'] : '',
                    'project_id'                => $this->resource['project_id'] ? obfuscate($this->resource['project_id']) : '',
                    'created_by'                => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
                    'permissions'               => $this->resource['permissions'] ? obfuscate_multiple($this->resource['permissions'], true) : '',
                    'created_at'                => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
                    'updated_at'                => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : ''
                ];
    }
}
