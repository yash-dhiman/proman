<?php

namespace App\Http\Resources\api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPUnit\Framework\assertTrue;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                    'project_id'            => $this->resource['project_id'] ? obfuscate($this->resource['project_id']) : '',
                    'project_title'         => $this->resource['project_title'] ? $this->resource['project_title'] : '',
                    'project_description'   => $this->resource['project_description'] ? $this->resource['project_description'] : '',
                    'start_date'            => $this->resource['start_date'] ? format_date($this->resource['start_date']) : '',
                    'end_date'              => $this->resource['end_date'] ? format_date($this->resource['end_date']) : '',
                    'assignees'             => obfuscate_multiple($this->resource['assignees'], true),
                    'category_id'           => $this->resource['category_id'] ? obfuscate($this->resource['category_id']) : '',
                    'status_id'             => $this->resource['status_id'] ? obfuscate($this->resource['status_id']) : '',
                    'created_by'            => $this->resource['created_by'] ? obfuscate($this->resource['created_by']) : '',
                    'created_at'            => $this->resource['created_at'] ? format_date_time($this->resource['created_at']) : '',
                    'updated_at'            => $this->resource['updated_at'] ? format_date_time($this->resource['updated_at']) : '',
                    'updated_by'            => $this->resource['updated_by'] ? obfuscate($this->resource['updated_by']) : '',
                    'deleted'               => $this->resource['deleted'] ? true : false,
                    'deleted_by'            => $this->resource['deleted_by'] ? obfuscate($this->resource['deleted_by']) : '',
                ];
    }
}