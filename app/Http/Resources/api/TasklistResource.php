<?php

namespace App\Http\Resources\api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TasklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tasklist_id'               => $this->resource['tasklist_id'] ? obfuscate($this->resource['tasklist_id']) : '',
            'tasklist_title'            => $this->resource['tasklist_title'] ? $this->resource['tasklist_title'] : '',
            'tasklist_description'      => $this->resource['tasklist_description'] ? $this->resource['tasklist_description'] : '',
            // 'assignees'                 => $this->resource['assignees'] ? obfuscate_multiple($this->resource['assignees'], true) : [],
            'created_by'                => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
            'created_at'                => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
            'updated_at'                => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : '',
            'updated_by'                => $this->resource['updated_by'] ? obfuscate($this->resource['updated_by']) : '',
            'deleted'                   => $this->resource['deleted'] ? true : false,
            'deleted_by'                => $this->resource['deleted_by'] ? obfuscate($this->resource['deleted_by']) : '',
        ];
    }
}
